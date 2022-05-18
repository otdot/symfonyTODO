<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Flasher\SweetAlert\Prime\SweetAlertFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CrudController extends AbstractController
{


    #[Route('/crud', name: 'crud', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Task::class)->findAll();
        $data = [];

        $dateNow = new \Datetime(date("Y-m-d"));

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'task' => $product->getTodo(),
                'date' => $product->getDate(),
                'isDone' => $product->isDone(),
                'daysDue' => date_diff($dateNow, $product->getDate())->format('%R%a'),
            ];
        }

        return $this->render("pages/homepage.html.twig", ["tasks" => $data, "dateNow" => $dateNow]);
    }

    #[Route("/create", name: "create")]
    public function create(SweetAlertFactory $flasher, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $task = trim($request->get("task"));
        $date = new \DateTime($request->get("date"));
        $done = false;
        if (empty($task)) {
            return $this->redirectToRoute("crud");
        }else {
            $todo = new Task();
            $todo->setTodo($task);
            $todo->setDate($date);
            $todo->setDone($done);
            $entityManager->persist($todo);
            $entityManager->flush();
            
            $flasher->addSuccess('New task added!');
            
            return $this->redirectToRoute("crud");
        }
    }

    #[Route("/update/{id}", name: "update")]
    public function update($id, SweetAlertFactory $flasher, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);
        $task->setDone(!$task->isDone());
        $entityManager->flush();
        return $this->redirectToRoute("crud");
    }

    #[Route("/delete/{id}", name: "delete")]
    public function delete($id, SweetAlertFactory $flasher, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);
        $name = $task->getTodo();
        $entityManager->remove($task);
        $entityManager->flush();
        $flasher->addInfo('task ' . "\"" . $name . "\"" . " deleted");
        return $this->redirectToRoute("crud");
    }
}
