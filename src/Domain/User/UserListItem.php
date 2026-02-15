<?php

namespace App\Domain\User;

use App\Domain\Shared\Address;

use App\Domain\File\TimedMedia;

use App\Domain\File\StaticMedia;
use App\Domain\JobOffer\JobListItem;

class UserListItem
{
     public function __construct(
        /** */
        public string $name,

         /** */
        public string $email,

         /** */
        public string $siret,

        public Address $address,

         /** @var array<StaticMedia> */
        public array $images = [],

         /** @var  TimedMedia*/
        public ?TimedMedia $presentation =null,

         /**  @var array<JobListItem> **/
        public array $job_offers = [],
     ){}
}