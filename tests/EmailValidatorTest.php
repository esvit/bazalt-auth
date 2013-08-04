<?php

namespace tests;

use Bazalt\Data\Validator;

class EmailValidatorTest extends \tests\BaseCase
{
    //http://svn.apache.org/viewvc/commons/proper/validator/trunk/src/test/java/org/apache/commons/validator/routines/EmailValidatorTest.java?view=markup
    protected $validEmails = [
        "jsmith@apache.org",
        "someone@[216.109.118.76]",
        "someone@yahoo.com",
        "jsmith@apache.com",
        "jsmith@apache.net",
        "jsmith@apache.info",
        "someone@yahoo.museum",
        "andy.noble@data-workshop.com",
        "andy.o'reilly@data-workshop.com",
        "foo+bar@i.am.not.in.us.example.com",
        " joeblow@apache.org",
        "joeblow@apache.org ",
        "joe!/blow@apache.org",
        "joe.ok@apache.org",
        "joe1blow@apache.org",
        'joe$blow@apache.org',
        "joe-@apache.org",
        "joe_@apache.org",
        "joe+@apache.org",
        "joe!@apache.org",
        "joe*@apache.org",
        "joe'@apache.org",
        "joe%45@apache.org",
        "joe?@apache.org",
        "joe&@apache.org",
        "joe=@apache.org",
        "+joe@apache.org",
        "!joe@apache.org",
        "*joe@apache.org",
        "'joe@apache.org",
        "%joe45@apache.org",
        "?joe@apache.org",
        "&joe@apache.org",
        "=joe@apache.org",
        "+@apache.org",
        "!@apache.org",
        "*@apache.org",
        "'@apache.org",
        "%@apache.org",
        "?@apache.org",
        "&@apache.org",
        "=@apache.org",
        "\"joe.\"@apache.org",
        "\".joe\"@apache.org",
        "\"joe+\"@apache.org",
        "\"joe!\"@apache.org",
        "\"joe*\"@apache.org",
        "\"joe'\"@apache.org",
        "\"joe(\"@apache.org",
        "\"joe)\"@apache.org",
        "\"joe,\"@apache.org",
        "\"joe%45\"@apache.org",
        "\"joe;\"@apache.org",
        "\"joe?\"@apache.org",
        "\"joe&\"@apache.org",
        "\"joe=\"@apache.org",
        "\"..\"@apache.org",
        "abc-@abc.com",
        "abc_@abc.com",
        "abc-def@abc.com",
        "abc_def@abc.com"
    ];

    protected $invalidEmails = [
        "jsmith@apache.",
        //"jsmith@apache.c",
        //"someone@yahoo.mu-seum",
        "andy-noble@data-workshop.-com",
        //"andy-noble@data-workshop.c-om",
        //"andy-noble@data-workshop.co-m",
        "andy.noble@data-workshop.com.",
        "andy.noble@\u008fdata-workshop.com",
        "andy@o'reilly.data-workshop.com",
        "foo+bar@example+3.com",
        "test@%*.com",
        "test@^&#.com",
        "joeblow@apa,che.org",
        "joeblow@apache.o,rg",
        "joeblow@apache,org",
        "joeblow @apache.org",
        "joeblow@ apache.org",
        "joe blow@apache.org ",
        "joeblow@apa che.org ",
        "joe@ap/ache.org",
        "joe@apac!he.org",
        "joe.@apache.org",
        ".joe@apache.org",
        ".@apache.org",
        "joe..ok@apache.org",
        "..@apache.org",
        "joe(@apache.org",
        "joe)@apache.org",
        "joe,@apache.org",
        "joe;@apache.org",
        "abc@abc_def.com"
    ];


    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testEmail()
    {
        foreach ($this->validEmails as $email){
            $data = Validator::create([
                'email' => $email
            ]);

            $data->field('email')->required()->email();

            $this->assertTrue($data->validate(), 'E-mail "' . $email . '" is invalid');
        }
        foreach ($this->invalidEmails as $email){
            $data = Validator::create([
                'email' => $email
            ]);

            $data->field('email')->required()->email();

            $this->assertFalse($data->validate(), 'E-mail "' . $email . '" is valid');
        }
    }
}