<?php

namespace Rubix\ML\Tests\CrossValidation;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\KFold;
use Rubix\ML\CrossValidation\Validator;
use Rubix\ML\Classifiers\DummyClassifier;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use PHPUnit\Framework\TestCase;

class KFoldTest extends TestCase
{
    protected $dataset;

    protected $estimator;

    protected $validator;

    public function setUp()
    {
        $this->dataset = Labeled::load(dirname(__DIR__) . '/iris.dataset');

        $this->estimator = new DummyClassifier();

        $this->validator = new KFold(10, false);
    }

    public function test_build_validator()
    {
        $this->assertInstanceOf(KFold::class, $this->validator);
        $this->assertInstanceOf(Validator::class, $this->validator);
    }

    public function test_test_estimator()
    {
        $score = $this->validator->test($this->estimator, $this->dataset, new Accuracy());

        $this->assertEquals(.5, $score, '', .5);
    }
}
