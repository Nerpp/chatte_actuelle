<?php

namespace App\DataFixtures;

use App\Entity\Articles;
use App\Entity\Comments;
use App\Entity\Edito;
use App\Entity\Tags;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $edito = 'orem ipsum dolor sit amet, consectetur adipiscing elit. Sed at lacus a risus cursus vestibulum. Mauris eget felis nec nunc convallis sagittis. Proin mi velit, maximus nec aliquet a, mattis in purus. Suspendisse vel eleifend ex. Suspendisse suscipit eget mauris at bibendum. Integer convallis malesuada lectus, id auctor lectus auctor ut. Vestibulum sit amet felis vitae quam semper volutpat nec nec urna.

        Nam tristique non turpis at efficitur. Sed faucibus sem non tellus efficitur, sit amet gravida nulla pharetra. Praesent sed auctor nisi. Aliquam lobortis erat velit, ornare scelerisque neque ullamcorper in. Cras sit amet enim in lectus tempor maximus vitae a ex. Duis sagittis consequat tortor, vel pellentesque enim. Quisque ullamcorper dui ex, iaculis volutpat ante commodo aliquet. Nullam turpis nunc, commodo id malesuada vitae, porttitor vel nunc. Ut sed euismod justo. Donec id nulla ultricies, venenatis metus et, lacinia arcu. Nunc accumsan mi ac placerat fermentum. Maecenas mollis, lorem eu ultricies eleifend, libero mi rutrum ipsum, id dapibus diam ligula interdum urna. In hac habitasse platea dictumst. Nunc ut metus ut urna dignissim efficitur eu ac risus.
        
        Etiam semper, ex lacinia luctus bibendum, tortor eros faucibus enim, in maximus justo elit quis turpis. Donec ut ex lectus. Nulla dignissim sapien eu dui blandit, id convallis justo tincidunt. Ut mi lorem, vulputate vel imperdiet eget, ultricies in augue. Mauris dictum est ex, a mollis odio eleifend vitae. Praesent sagittis enim justo, id tempus ante lacinia laoreet. Nam semper lacus in placerat iaculis. Nam et neque eu enim tempor eleifend vitae in diam. Mauris tristique maximus magna ac varius. Aliquam velit elit, laoreet vel nisl non, blandit hendrerit ipsum. Curabitur a tellus eget mauris accumsan laoreet. Curabitur tristique, ex porttitor dignissim fringilla, libero augue tempus ipsum, in bibendum nisl leo at ipsum. Pellentesque eu fermentum dolor. Mauris sollicitudin porttitor neque ut volutpat.
        
        In sit amet tortor elit. Nunc ex dolor, euismod eu lorem sed, scelerisque bibendum ex. Sed sit amet auctor lorem. Phasellus nec nisi vel dui aliquet luctus non vel erat. Nam vulputate vestibulum nunc. Pellentesque dignissim quis lectus sed vestibulum. Suspendisse consectetur urna fringilla enim faucibus, sed volutpat.';

        $listUser = [
            [
                'email' => 'regiaurelien@gmail.com',
                'displayName' => 'Aurélien',
                'password' => '12345678',
                'roles' => ['ROLE_SUPERADMIN'],
                'isVerified' => true
            ],
            [
                'email' => 'leblancanais@gmail.com',
                'displayName' => 'Anais',
                'password' => '12345678',
                'roles' => ['ROLE_ADMIN'],
                'isVerified' => true
            ],
            [
                'email' => 'francis@gmail.com',
                'displayName' => 'Francis',
                'password' => '12345678',
                'roles' => ['ROLE_USER'],
                'isVerified' => true
            ],
            [
                'email' => 'darky@gmail.com',
                'displayName' => 'Dark Vador',
                'password' => '12345678',
                'roles' => ['ROLE_USER'],
                'isVerified' => true
            ],
            [
                'email' => 'fernande@gmail.com',
                'displayName' => 'Fernande',
                'password' => '12345678',
                'roles' => ['ROLE_USER'],
                'isVerified' => true
            ],
            [
                'email' => 'line@gmail.com',
                'displayName' => 'Linette',
                'password' => '12345678',
                'roles' => ['ROLE_USER'],
                'isVerified' => true
            ],

        ];

        $listTags = [
            [
                'tags' => 'tag 1',
                'color'=> 'red'
            ],
            [
                'tags' => 'tag 2',
                'color'=> 'yellow'
            ],
            [
                'tags' => 'tag 3',
                'color'=> 'blue'
            ],
            [
                'tags' => 'tag 4',
                'color'=> 'purple'
            ],
            [
                'tags' => 'tag 5',
                'color'=> 'green'
            ],
        ];

        $listArticle = [
            
                'title' => 'Titre',
                'slug' => 'titre',
                'article' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed at lacus a risus cursus vestibulum. Mauris eget felis nec nunc convallis sagittis. Proin mi velit, maximus nec aliquet a, mattis in purus. Suspendisse vel eleifend ex. Suspendisse suscipit eget mauris at bibendum. Integer convallis malesuada lectus, id auctor lectus auctor ut. Vestibulum sit amet felis vitae quam semper volutpat nec nec urna.

                Nam tristique non turpis at efficitur. Sed faucibus sem non tellus efficitur, sit amet gravida nulla pharetra. Praesent sed auctor nisi. Aliquam lobortis erat velit, ornare scelerisque neque ullamcorper in. Cras sit amet enim in lectus tempor maximus vitae a ex. Duis sagittis consequat tortor, vel pellentesque enim. Quisque ullamcorper dui ex, iaculis volutpat ante commodo aliquet. Nullam turpis nunc, commodo id malesuada vitae, porttitor vel nunc. Ut sed euismod justo. Donec id nulla ultricies, venenatis metus et, lacinia arcu. Nunc accumsan mi ac placerat fermentum. Maecenas mollis, lorem eu ultricies eleifend, libero mi rutrum ipsum, id dapibus diam ligula interdum urna. In hac habitasse platea dictumst. Nunc ut metus ut urna dignissim efficitur eu ac risus.
                
                Etiam semper, ex lacinia luctus bibendum, tortor eros faucibus enim, in maximus justo elit quis turpis. Donec ut ex lectus. Nulla dignissim sapien eu dui blandit, id convallis justo tincidunt. Ut mi lorem, vulputate vel imperdiet eget, ultricies in augue. Mauris dictum est ex, a mollis odio eleifend vitae. Praesent sagittis enim justo, id tempus ante lacinia laoreet. Nam semper lacus in placerat iaculis. Nam et neque eu enim tempor eleifend vitae in diam. Mauris tristique maximus magna ac varius. Aliquam velit elit, laoreet vel nisl non, blandit hendrerit ipsum. Curabitur a tellus eget mauris accumsan laoreet. Curabitur tristique, ex porttitor dignissim fringilla, libero augue tempus ipsum, in bibendum nisl leo at ipsum. Pellentesque eu fermentum dolor. Mauris sollicitudin porttitor neque ut volutpat.
                
                In sit amet tortor elit. Nunc ex dolor, euismod eu lorem sed, scelerisque bibendum ex. Sed sit amet auctor lorem. Phasellus nec nisi vel dui aliquet luctus non vel erat. Nam vulputate vestibulum nunc. Pellentesque dignissim quis lectus sed vestibulum. Suspendisse consectetur urna fringilla enim faucibus, sed volutpat.'
        ];

        $ListComments = [

        ];

        foreach ($listUser as $userListed) {
            $user = new User;
            $user->setEmail($userListed['email']);
            $user->setDisplayname($userListed['displayName']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userListed['password']));
            $user->setRoles($userListed['roles']);
            $manager->persist($user);
            $allUser[] = $user;
        }

        $insertEdito = new Edito;
        $insertEdito->setEdito($edito);
        $insertEdito->setPublishedAt(new \DateTime('now'));
        $manager->persist($insertEdito);

        foreach ($listTags as $tagListed) {
           $tag = new Tags;
           $tag->setName($tagListed['tags']);
           $manager->persist($tag);
           $allTags[] = $tag;
        }

        $manager->flush();

        for ($i=0; $i < 11; $i++) { 
            foreach ($allUser as $userALl) {
               
                $article = new Articles;
                $IdUnic = uniqid(true);
                $article->setTitle($listArticle['title'].$IdUnic);
                $article->setSlug($listArticle['slug'].$IdUnic);
                $article->setArticle($listArticle['article']);

                $draft = rand(0,1);
                $article->setDraft($draft);
                if ($draft === 0) {
                    $article->setPublishedAt(new \DateTime('now'));
                }
                $article->setUser($userALl);
                $article->setTags($allTags[rand(0,4)]);
                $manager->persist($article);
                $allArticle[] = $article;

            }
        }

        foreach ($allArticle as $articleAll) {

           for ($i=0; $i <15 ; $i++) { 
            $comment = new Comments;
            $comment->setComment(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis sem ligula. Nulla nisl ex, rhoncus et rutrum vel, tempor nec lorem. Pellentesque sit amet augue vitae ipsum blandit pharetra. Nullam imperdiet imperdiet semper. Vestibulum vivamus.'
        );
            $comment->setCreatedAt(new \DateTime('+' . mt_rand(5, 19) . 'days'));
            $comment->setUser($allUser[rand(0,count($allUser)-1)]);
            $comment->setArticle($articleAll);
            $manager->persist($comment);
           }
        }

        $manager->flush();
    }
}
