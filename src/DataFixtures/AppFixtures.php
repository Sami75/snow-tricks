<?php
namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Comment;

use App\Service\UploadedFileManager;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    private $encoder;
    private $uploadedFile;

    public function __construct(UserPasswordEncoderInterface $encoder, UploadedFileManager $uploadedFile, $rootDirectory)
    {
        $this->encoder = $encoder;
        $this->uploadedFile = $uploadedFile;
        $this->rootDirectory = $rootDirectory;
    }
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setNickName('admin');

        $user->setEmail('admin@admin.com');

        $password = $this->encoder->encodePassword($user, 'Pass_1234');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();


        for ($i=0; $i < 10 ; $i++) {
            $trick = new Trick();
            $trick->setName("trick " . ($i+1));
            $trick->setDescription("Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte. Il n'a pas fait que survivre cinq siècles, mais s'est aussi adapté à la bureautique informatique, sans que son contenu n'en soit modifié. Il a été popularisé dans les années 1960 grâce à la vente de feuilles Letraset contenant des passages du Lorem Ipsum, et, plus récemment, par son inclusion dans des applications de mise en page de texte, comme Aldus PageMaker");
            $trick->setNiveau(mt_rand(1, 4));
            $trick->setTrickGroup(mt_rand(1, 3));
            // $trick->addImgDoc("4002d37a3879c2d63c8e6aed7ad31449.jpeg");
            $imageFile = [];

            if (is_file($this->rootDirectory .'/assets/images/uploads/images/img-'. ($i+1) .'.jpg')) {
                $imageFile[] = new File($this->rootDirectory .'/assets/images/uploads/images/img-'. ($i+1) .'.jpg', 'img-'. ($i+1) .'.jpg', 'image/jpeg', null, null, true);
            }

            $videoFile = [];

            if (is_file($this->rootDirectory .'/assets/images/uploads/images/video-'. ($i+1) .'.mp4')) {
                $videoFile[] = new File($this->rootDirectory .'/assets/images/uploads/images/video-'. ($i+1) .'.mp4', 'video-'. ($i+1) .'.mp4', 'video/mp4', null, null, true);
            }


            $trick->setImgDocs($this->uploadedFile->docsInputManager($imageFile));
            $trick->setVideoDocs($this->uploadedFile->docsInputManager($videoFile));

            for ($ii=0; $ii < 5; $ii++) {
                $comment = new comment();
                $comment->setContent(substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 5)), 0, 7));
                $comment->setTrick($trick);
                $comment->setUser($user);
                $manager->persist($comment);
            }

            $manager->persist($trick);
        }
        $manager->flush();
    }
}
