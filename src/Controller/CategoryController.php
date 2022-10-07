<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
    }

    #[Route('/category', name: 'app_category')]
    public function index(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $method = $request->getMethod();
        $categories = [];
        switch ($method) {
            case 'GET':
                $test = $categoryRepository->findAll();
                foreach ($test as $item) {
                    $categories[] = ['id' => $item->getId(), 'name' => $item->getName(), "image" => $item->getImage()];
                }
                return $this->json($categories);
                break;
            case 'POST':
                // create a new category
                break;
            case 'PUT':
                // return $this->updateCategory($request);
                break;
            case 'DELETE':
                //return $this->deleteCategory($request);
                break;
            default:
                return $this->json([
                    'message' => 'Method not allowed',
                ], 405);
        }

        return $this->json([
            'message' => $method,
            'path' => 'src/Controller/CategoryController.php',
        ]);
    }

    #[Route('/best-category', name: 'app_category_best')]
    public function bestCategory(CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->bestCategory();
        $data = [];
        foreach ($category as $item) {
            $data[] = ['id' => $item->getId(), 'name' => $item->getName(), "image" => $item->getImage()];
        }

        return $this->json($data, 200);
    }
}
