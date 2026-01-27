<?php

namespace App\Api\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This one is used to serve a page to the client
 * to mange and test the embeded Api;
 * Its must be used for code developpment purposes!!
 */

class ApiViewManager extends AbstractController
{
    #[Route("/manager", name:"api_tester")]
    public function show(Request $request): Response
    {
        return $this->render("/Api/index.html.twig");
    }

    #[Route("/api-manager-secure-js", name: "secure-js")] //For developper to test api, controller to be accessible temporary
    public function serveJs(): Response
    {
        //------SECURITY
            throw new \Exception('Not implemented');
        //--------------

        $jsPath    = $this->getParameter("kernel.project_dir") . "/src/Infrastructure/Storage/Vault/scripts/test-manager.js";
        $jsContent = file_get_contents($jsPath);
        return new Response(
            $jsContent,
            200,
            ['Content-Type' => "application/javascript"]
        );
    }
}


?>