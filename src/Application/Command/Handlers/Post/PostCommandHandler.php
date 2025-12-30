<?php

namespace App\Application;

use Exception;

use App\Domain\Model\PostData;

use App\Domain\Usecases\PostCommandHandlerInteface;
use Symfony\Component\Validator\Constraints as Assert;

use App\Infrastructure\Persistence\MySQL\Repositories\PostRepository;
use App\Infrastructure\Validators\PostValidator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class PostCommandHandler implements PostCommandHandlerInteface
{

    public function __construct(private PostRepository $postRepository, private PostValidator $validator){}
    

    public function findAllPost(): array{
        try{
            $allPosts = $this->postRepository->findAll();
            return $allPosts;
        }
        catch(Exception $ex){
            throw $ex;
        }
    }


    public function findOnePost(string $uuid): ?object{
        try{
            //---Fields Validation
            $errors = $this->validator->validate($uuid, [new Assert\NotNull(), new Assert\Length(min: 36)]);
            if(count($errors) > 0){
                throw new BadRequestHttpException("A Key argument is missing or is in bad format");
            }
            $onePost = $this->postRepository->findOne($uuid);
            return $onePost;
        }
        catch(Exception $ex){
            throw $ex;
        }
    }


    public function createPost(array $json): void
    {
        try{
            //---Data transfert object
            $postData  = new PostData();
            
            //---Fields Validation
            $postData->title =  $json["title"] ?? null;
            $postData->content = $json["content"] ?? [];
            $errors = $this->validator->validate($postData, groups: ['create']);

            if(count($errors) > 0){
                throw new BadRequestHttpException("A Key argument is missing or is in bad format");
            }

            //---Repository
            $this->postRepository->create(title: $postData->title, content: $postData->content);
        }
        catch(Exception $ex){
            throw $ex;
        }
    }
}


?>