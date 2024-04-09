<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Faker\Factory;

#[Route('/api/product')]
class ProductController extends AbstractController
{

    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->json($productRepository->findAll(),200,[],['groups'=>'group:product-all']);

    }

    #[Route('/faker', name: 'app_product_faker', methods: ['GET'])]
    public function faker(ProductRepository $productRepository,EntityManagerInterface $entityManager): Response
    {
        if (! in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->json('User no admin', 200);
        }
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true)); // Génère un nom composé de 3 mots

            $product->setDescription($faker->words(25,true));

            $product->setPrice($faker->randomFloat(2, 10, 100)); // Génère un prix aléatoire entre 10 et 100 avec 2 décimales

            $entityManager->persist($product);
        }
        $entityManager->flush();
        return $this->json($productRepository->findAll(),200,[],['groups'=>'group:product-all']);

    }
    /*#[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }*/

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->json($product,200,[],['groups'=>'group:product-all']);
    }
    #[Route('/qr/{id}', name: 'app_product_qr', methods: ['GET'])]
    public function qr(Product $product,BuilderInterface $builder,Request $request)  : Response
    {
        $routeName=$this->generateUrl("app_product_show", ['id' => $product->getId()]);
        $host = $request->getHost();
        $url="";
        if ($_ENV['APP_ENV'] === 'dev') {
            $url=$host.":8000".$routeName;
        } else {
            $host.":80".$routeName;
        }

        $resultBuilder = $builder->size(400)->margin(20 )->data($url)->build();

        $base64= $resultBuilder->getDataUri();

        $html = '<img src="' . $base64 . '">';

        return new Response('<html lang="fr"><body>'.$html.'</body></html>');
    }

    /*#[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }*/
}
