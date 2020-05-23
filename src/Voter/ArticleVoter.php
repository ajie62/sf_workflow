<?php

namespace App\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    const ARTICLE_EDIT = 'edit';
    const ARTICLE_DELETE = 'delete';

    const ATTRIBUTES = [
        self::ARTICLE_EDIT,
        self::ARTICLE_DELETE,
    ];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, self::ATTRIBUTES, true) && $subject instanceof Article;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        /** @var Article $article */
        $article = $subject;

        switch ($attribute) {
            case self::ARTICLE_EDIT:
                return $this->canEdit($article, $user);
            case self::ARTICLE_DELETE:
                return $this->canDelete($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Article $article, User $user): bool
    {
        $isDraftArticle = 'draft' === $article->getStatus();
        $isSpellchecker = in_array(User::USER_SPELLCHECKER, $user->getRoles());
        $isArticleAuthor = $article->getAuthor() === $user;

        // The article can be edited only if its status is 'draft' and the user
        // has a role of spellchecker, or the user is the article's author.
        return $isDraftArticle && $isSpellchecker || $isArticleAuthor;
    }

    private function canDelete(Article $article, User $user): bool
    {
        // An admin is able to delete an article for whatever reason,
        // otherwise only the article's author is allowed to do it.
        $isAdmin = in_array(User::USER_ADMIN, $user->getRoles());
        $isArticleAuthor = $article->getAuthor() === $user;

        return $isAdmin || $isArticleAuthor;
    }
}
