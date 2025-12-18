<?
namespace App\Usecases\Post;

use App\Domain\Model\Post;
use App\Infrastructure\Persistence\MySQL\Repository\PostRepository;
use App\Usecases\Post\PostReader as PostPostReader;

class PostReader implements PostPostReader
{

    public function __construct(private PostRepository $postRepository){}
    /**
     * @return object[]
     */
    public function findAll(): array{
        $allPosts = $this->postRepository->findAll();
        return $allPosts;
    }

    public function findOne(int $id): Post | null{
        $onePost = $this->postRepository->findOne($id);
        return $onePost;
    }
}