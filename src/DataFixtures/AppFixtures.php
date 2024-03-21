<?php

// src/DataFixtures/AppFixtures.php

// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Comments;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // Create users
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@example.com');
            $user->setPassword('password');
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);

            // Create articles for each user
            for ($j = 1; $j <= 3; $j++) {
                $article = new Articles();
                $article->setTitle('Article ' . $j . ' for User ' . $i);
                $article->setContent('Content of Article ' . $j . ' for User ' . $i);
                $article->setUser($user);

                // Set the date for the article
                $article->setDate(new \DateTime());
                $coverImage = sprintf('https://example.com/images/article_%d.jpg', $i);
                $article->setCoverImage($coverImage);


                $manager->persist($article);

                $manager->persist($article);

                // Create comments for each article
                for ($k = 1; $k <= 2; $k++) {
                    $comment = new Comments();
                    $comment->setContent('Comment ' . $k . ' on Article ' . $j . ' for User ' . $i);
                    $comment->setUser($user);
                    $comment->setArticles($article);

                    // Set the date for the comment
                    $comment->setDate(new \DateTime());

                    $manager->persist($comment);
                }
            }
        }

        // Create categories
        $categoryTitles = ['Category A', 'Category B', 'Category C'];
        foreach ($categoryTitles as $title) {
            $category = new Categories();
            $category->setCategoryTitle($title);
            $manager->persist($category);
        }

        $manager->flush();
    }
}