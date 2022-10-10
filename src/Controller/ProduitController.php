<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Produit;

;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit', methods: ["GET", "POST", "PUT", "DELETE"])]
    public function index(Request $request, ManagerRegistry $managerRegistry): JsonResponse
    {
        $method = $request->getMethod();
        $produit = new Produit();


        switch ($method) {

            case "GET":
                $produits = $managerRegistry->getRepository(Produit::class)->findAll();
                $data = [];
                foreach ($produits as $produit) {
                    $data[] = [
                        "id" => $produit->getId(),
                        "name" => $produit->getName(),
                        "price" => $produit->getPrice(),
                        "marque" => $produit->getMarque(),
                        "thumbnail" => $produit->getThumbnailImg(),
                        "image" => $produit->getPhotos(),
                        "category" => $produit->getCategory()->getName()
                    ];
                }
                return $this->json([
                    'data' => $data,
                ], 200);
                break;
            case "POST":
                if ($request->query->get("name") && $request->query->get("price") && $request->query->get("marque") && $request->query->get("thumbnailImg") && $request->query->get("photos") && $request->query->get("category")) {
                    $name = $request->query->get("name");
                    $price = $request->query->get("price");
                    $marque = $request->query->get("marque");
                    $thumbnailImg = $request->query->get("thumbnailImg");
                    $photos = $request->query->get("photos");
                    $category = $request->query->get("category");
                    $produit->setName($name);
                    $produit->setPrice($price);
                    $produit->setMarque($marque);
                    $produit->setThumbnailImg($thumbnailImg);
                    $produit->setPhotos($photos);
                    $produit->setCategory($managerRegistry->getRepository(Category::class)->findOneBy(["name" => $category]));
                    $managerRegistry->getManager()->persist($produit);
                    $managerRegistry->getManager()->flush();
                    return $this->json([
                        'data' => [
                            "id" => $produit->getId(),
                            "name" => $produit->getName(),
                            "price" => $produit->getPrice(),
                            "marque" => $produit->getMarque(),
                            "thumbnail" => $produit->getThumbnailImg(),
                            "image" => $produit->getPhotos(),
                            "category" => $produit->getCategory()->getName()
                        ],
                    ], 200);
                } else {
                    return $this->json([
                        'data' => "data missing",
                    ], 200);
                }
                break;
            case "PUT":
                $produit = $managerRegistry->getRepository(Produit::class)->findOneBy(["id" => $request->query->get("id")]);
                $this->update($produit, $request, $managerRegistry);
                return $this->json([
                    'message' => 'PUT',
                    'path' => 'src/Controller/ProduitController.php',
                ]);
                break;
            case "DELETE":
                $produit = $managerRegistry->getRepository(Produit::class)->findOneBy(["id" => $request->query->get("id")]);
                if ($produit) {
                    $managerRegistry->getManager()->remove($produit);
                    $managerRegistry->getManager()->flush();
                    return $this->json([
                        'message' => 'DELETE',
                    ]);
                } else {
                    return $this->json([
                        'message' => 'Produit not found',
                        'path' => 'src/Controller/ProduitController.php',
                    ]);
                }
                break;
            default:
                return $this->json([
                    'message' => 'Method not allowed',
                ], 405);

        }
    }

    #[Route('/produit/{id}', name: 'app_produit_get_one', methods: ['GET'])]
    public function getOne($id, ManagerRegistry $managerRegistry): JsonResponse
    {
        $produit = $managerRegistry->getRepository(Produit::class)->findOneBy(["id" => $id]);
        if ($produit) {
            $data = [
                "id" => $produit->getId(),
                "name" => $produit->getName(),
                "price" => $produit->getPrice(),
                "marque" => $produit->getMarque(),
                "thumbnail" => $produit->getThumbnailImg(),
                "image" => $produit->getPhotos(),
                "category" => $produit->getCategory()->getName()
            ];
            return $this->json([
                'data' => $data,
            ], 200);
        } else {
            return $this->json([
                'message' => 'Produit not found',
                'path' => 'src/Controller/ProduitController.php',
            ]);
        }
    }

    /**
     * @param mixed $produit
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @return void
     */
    public function update(mixed $produit, Request $request, ManagerRegistry $managerRegistry): void
    {
        if ($request->query->get("name")) {
            $produit->setName($request->query->get("name"));
        }
        if ($request->query->get("price")) {
            $produit->setPrice($request->query->get("price"));
        }
        if ($request->query->get("marque")) {
            $produit->setMarque($request->query->get("marque"));
        }
        if ($request->query->get("thumbnail")) {
            $produit->setThumbnailImg($request->query->get("thumbnail"));
        }
        if ($request->query->get("image")) {
            $produit->setPhotos($request->query->get("image"));
        }
        if ($request->query->get("category")) {
            $category = $managerRegistry->getRepository(Category::class)->findOneBy(["name" => $request->query->get("category")]);
            $produit->setCategory($category);
        }
        $managerRegistry->getManager()->persist($produit);
        $managerRegistry->getManager()->flush();
    }
}
