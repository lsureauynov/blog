// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Articles;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadArticles($manager);
        $this->loadCategories($manager);

        // Add more fixture loading methods as needed
    }

    private function loadUsers(ObjectManager $manager)
    {
        // Create a user with specific data
        $user = new User();
        $user->setUsername('john_doe');
        $user->setEmail('john.doe@example.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'password123'));
        $manager->persist($user);
        $manager->flush();

        // Add more users as needed
    }
    private function loadArticles(ObjectManager $manager){
        // Create an article with specific data
        $article = new Articles();
        $article->setTitle('Sample Article');
        $article->setContent('This is the content of the sample article.');
        // Set other properties as needed
        $manager->persist($article);

        // Add more articles as needed

        $manager->flush();
    }
    private function loadCategories(ObjectManager $manager){

    // Create a category with specific data
        $category = new Categories();
        $category->setName('Sample Category');
        $category->setDescription('This is the description of the sample category.');
        // Set other properties as needed
        $manager->persist($category);

        // Add more categories as needed

        $manager->flush();
    }
    private function loadComments(ObjectManager $manager){

    // Create a comment with specific data
        $comment = new Comments();
        $comment->setAuthor('John Doe');
        $comment->setContent('This is a sample comment.');
        // Set other properties as needed
        $manager->persist($comment);

        // Add more comments as needed

        $manager->flush();
    }
    // Add more fixture loading methods for other entities as needed
}
