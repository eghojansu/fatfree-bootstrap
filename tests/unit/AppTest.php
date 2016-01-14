<?php

namespace tests\unit;

use App;

class AppTest extends \tests\Test
{
    public function get($app,$params)
    {
        $test = $this->newTest();

        $test->message('Testing App class');
        $methodOK = [
            'initBase', 'baseUrl', 'url', 'asset',
        ];
        $methodSkip = [
            'jsonOut',
        ];
        $test->message('::'.implode(', ::', $methodOK).' ok');
        $test->message('::'.implode(', ::', $methodSkip).' skipped');
        $db = App::database();
        $test->expect(
            $db instanceOf \DB\SQL,
            '::database');
        $map = App::map('gejala');
        $test->expect(
            $map instanceOf \DB\SQL\Mapper,
            '::map');
        $test->expect(
            $map->load()->valid(),
            '::map valid, content '.var_export($map->cast(),true));
        $newID = App::newID($map, 'kode_gejala', 'GJB{99}');
        $test->expect(
            !empty($newID),
            '::newID generate right new id '.$newID);
        $arraySource = ['b'=>10];
        $generatedArray = App::prependKey($arraySource);
        $expectedArray = [':b'=>10];
        $test->expect($expectedArray===$generatedArray,
            '::prependKey test');
        $true = true;
        foreach ([
            'eko kurniawan',
            'ekoKurniawan',
            'eko_kurniawan',
            ] as $value) {
            $true &= (App::titleIze($value)==='Eko Kurniawan');
        }
        $test->expect($true, '::titleIze');
        $className = App::className(get_called_class());
        $test->expect('AppTest'===$className, '::className is AppTest');
        $test->expect(1===count(App::dirContent(__DIR__.'/../ui')), 'App::dirContent');

        $test->expect('app_test'===App::classNametoTable(__CLASS__), '::classNametoTable is valid');

        $this->testResult($test);
    }
}