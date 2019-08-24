<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Login\OAuth;

use App\Security\UserManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GithubController
 *
 * @Route("/github", name="github_")
 *
 * @SWG\Tag(name="Login")
 */
class GithubController extends AbstractController
{
    /**
     * @var GithubClient
     */
    protected $client;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * GithubController constructor.
     *
     * @param ClientRegistry $clientRegistry
     * @param UserManager    $userManager
     */
    public function __construct(ClientRegistry $clientRegistry, UserManager $userManager)
    {
        $this->client      = $clientRegistry->getClient('github_main');
        $this->userManager = $userManager;
    }

    /**
     * @Route("/connect/", name="connect", methods={"get"})
     *
     * @SWG\Response(
     *     response="302",
     *     description="Redirects to Github OAuth page."
     * )
     */
    public function connectAction(): RedirectResponse
    {
        return $this->client->redirect(['email']);
    }

    /**
     * @Route("/check/", name="check")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function checkAction(Request $request): RedirectResponse
    {
        $this->client->setAsStateless();
        $oauthUser = $this->client->fetchUser();
        $user      = $this->userManager->getUserByGithubResourceOwner($oauthUser);
        $user->updateCode($request->query->get('code'));
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse(
            sprintf('%s?%s', $_ENV['APP_LOGIN_CALLBACK_URL'], $request->getQueryString()), 302
        );
    }
}
