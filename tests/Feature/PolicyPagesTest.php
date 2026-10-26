<?php

namespace Tests\Feature;

use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    public function test_policy_pages_are_public_and_identify_missing_business_details(): void
    {
        $this->withoutVite();
        config(['business.legal_name' => null]);

        foreach (config('policies') as $key => $policy) {
            $this->get('/'.$key)
                ->assertOk()
                ->assertSeeText($policy['title'])
                ->assertSeeText('Awaiting confirmation')
                ->assertSee(route('privacy-policy'), false)
                ->assertSee(route('refund-policy'), false);
        }

        $this->get('/terms-and-conditions')
            ->assertSeeText('ChainEarn EverPass')
            ->assertSeeText('one-time purchase')
            ->assertSeeText('no subscription, automatic renewal, recurring charge, or scheduled expiry');
    }
}
