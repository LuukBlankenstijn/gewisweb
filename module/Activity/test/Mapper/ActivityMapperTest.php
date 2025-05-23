<?php

declare(strict_types=1);

namespace ActivityTest\Mapper;

use Activity\Mapper\Activity as ActivityMapper;
use Activity\Model\Activity;
use Activity\Model\ActivityLocalisedText;
use ApplicationTest\Mapper\BaseMapperTrait;
use DateTime;
use Decision\Model\Enums\MembershipTypes;
use Decision\Model\Member;
use PHPUnit\Framework\TestCase;
use User\Model\User;

class ActivityMapperTest extends TestCase
{
    /** @use BaseMapperTrait<ActivityMapper, Activity> */
    use BaseMapperTrait;

    protected User $user;
    protected Member $member;
    private ActivityLocalisedText $localisedText;

    public function setUp(): void
    {
        $this->setUpEntityManager();

        $this->mapper = $this->serviceManager->get(ActivityMapper::class);

        $this->setUpUser();

        $this->localisedText = new ActivityLocalisedText('activity', 'activiteit');

        $this->object = new Activity();
        $this->object->setName($this->localisedText);
        $this->object->setDescription($this->localisedText);
        $this->object->setLocation($this->localisedText);
        $this->object->setCosts($this->localisedText);
        $this->object->setCreator($this->member);
        $this->object->setBeginTime(new DateTime());
        $this->object->setEndTime(new DateTime());
        $this->object->setStatus(Activity::STATUS_APPROVED);
        $this->object->setIsMyFuture(false);
        $this->object->setRequireGEFLITST(false);
        $this->object->setRequireZettle(false);
    }

    protected function setUpUser(): void
    {
        $this->user = new User();
        $this->user->setLidnr(8000);
        $this->user->setPassword('');

        $this->member = new Member();
        $this->member->setLidnr(8000);
        $this->member->setEmail('web@gewis.nl');
        $this->member->setInitials('W.C.');
        $this->member->setFirstName('Web');
        $this->member->setMiddleName('');
        $this->member->setLastName('Committee');
        $this->member->setGeneration(2020);
        $this->member->setType(MembershipTypes::Ordinary);
        $this->member->setChangedOn(new DateTime());
        $this->member->setBirth(new DateTime());
        $this->member->setExpiration(new DateTime());

        $this->user->setMember($this->member);

        $this->entityManager->persist($this->member);
        $this->entityManager->persist($this->user);
    }

    public function testGetUpcomingActivitiesForMember(): void
    {
        $this->mapper->getUpcomingActivitiesForMember($this->user);
        $this->expectNotToPerformAssertions();
    }
}
