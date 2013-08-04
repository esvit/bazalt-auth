<?php

namespace tests;

use Bazalt\Data\Validator;

class ValidatorTest extends \tests\BaseCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testRequired()
    {
        $data = Validator::create([
            'test' => 'test'
        ]);

        $data->field('test')->required();

        $this->assertTrue($data->validate());

        $data = Validator::create([
            'test' => 'test'
        ]);

        $data->field('test2')->required();

        $this->assertFalse($data->validate());
    }

    public function testEqual()
    {
        $data = Validator::create([
            'test' => 'test',
            'test2' => 'test',
            'test3' => 'test2',
            'test4' => 'test2'
        ]);

        $data->field('test')->equal($data['test2']);
        $data->field('test3')->equal($data['test4']);

        $this->assertTrue($data->validate());

        $data = Validator::create([
            'test' => 'test',
            'test2' => 'test2'
        ]);

        $data->field('test')->equal($data['test2']);

        $this->assertFalse($data->validate());
    }

    public function testBool()
    {
        $data = Validator::create([
            'test' => '1',
            'test2' => 'true',
            'test3' => 'on',
            'test4' => 'yes',
            'test5' => '0',
            'test6' => 'false',
            'test7' => 'off',
            'test8' => 'no',
            'test9' => ''
        ]);

        $data->field('test')->bool()->equal(true);
        $data->field('test2')->bool()->equal(true);
        $data->field('test3')->bool()->equal(true);
        $data->field('test4')->bool()->equal(true);
        $data->field('test5')->bool()->equal(false);
        $data->field('test6')->bool()->equal(false);
        $data->field('test7')->bool()->equal(false);
        $data->field('test8')->bool()->equal(false);
        $data->field('test9')->bool()->equal(false);

        $this->assertTrue($data->validate());

        $data = Validator::create([
            'test' => 'test'
        ]);

        $data->field('test')->bool();

        $this->assertFalse($data->validate());
        $this->assertEquals('test', $data['test']);
    }

    public function testFloat()
    {
        $data = Validator::create([
            'test' => '1',
            'test2' => '1.3',
            'test3' => '00.1',
            'test4' => '01.33',
            'test5' => '14141.1',
            'test6' => '1231231.123123'
        ]);

        $data->field('test')->float()->equal((double)1);
        $data->field('test2')->float()->equal(1.3);
        $data->field('test3')->float()->equal(0.1);
        $data->field('test4')->float()->equal(1.33);
        $data->field('test5')->float()->equal(14141.1);
        $data->field('test6')->float()->equal(1231231.123123);

        $this->assertTrue($data->validate(), json_encode($data->errors()));

        $data = Validator::create([
            'test' => 'test'
        ]);

        $data->field('test')->float();

        $this->assertFalse($data->validate(), json_encode($data->errors()));
        $this->assertEquals('test', $data['test']);
    }

    public function testInt()
    {
        $data = Validator::create([
            'test' => '1',
            'test2' => '112',
            'test3' => '0',
            'test4' => '133',
            'test5' => '141411',
            'test6' => '123123112'
        ]);

        $data->field('test')->int()->equal(1);
        $data->field('test2')->int(0, 150)->equal(112);
        $data->field('test3')->int()->equal(0);
        $data->field('test4')->int()->equal(133);
        $data->field('test5')->int()->equal(141411);
        $data->field('test6')->int()->equal(123123112);

        $this->assertTrue($data->validate(), json_encode($data->errors()));

        $data = Validator::create([
            'test' => 'test'
        ]);

        $data->field('test')->int();

        $this->assertFalse($data->validate(), json_encode($data->errors()));
        $this->assertEquals('test', $data['test']);

        $data = Validator::create([
            'test' => 200
        ]);

        $data->field('test')->int(0, 100);

        $this->assertFalse($data->validate());
    }

    public function testObjectAccess()
    {
        $obj = new \stdClass();
        $obj->test = 200;
        $data = Validator::create($obj);

        $data->field('test')->int(0, 200);

        $this->assertTrue($data->validate());
    }

    public function testNestedObject()
    {
        $obj = new \stdClass();

        $nested = new \stdClass();
        $nested->title = '123';

        $obj->data = $nested;

        $this->assertTrue(
            Validator::create($obj)
                ->field('data')->nested(
                    Validator::create()
                             ->field('title')
                             ->required()
                             ->equal('123')
                             ->end()
                )
            ->end()
            ->validate()
        );

        $this->assertFalse(
            Validator::create($obj)
                ->field('data')->nested(
                    Validator::create()
                             ->field('title')
                             ->required()
                             ->equal('some other string')
                             ->end()
                )
            ->end()
            ->validate()
        );
    }
}