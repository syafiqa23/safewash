<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function migrateFreshUsing()
    {
        return array_merge(parent::migrateFreshUsing(), ['--force' => true]);
    }
}
