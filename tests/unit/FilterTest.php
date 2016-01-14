<?php

namespace tests\unit;

use Filter;

class FilterTest extends \tests\Test
{
    public function get($app,$params)
    {
        $test = $this->newTest();
        $filter = Filter::instance();

        $test->message('Testing Filter class');

        $filterS = [
            'field_name as Field Name: [required]',
        ];
        $filterEx = [
            'field_name'=>[
                'name'=>'field_name',
                'alias'=>'Field Name',
                'filter'=>[
                    'required'=>[],
                ],
            ],
        ];
        $filterR = $filter->filterFilter($filterS);
        $test->expect($filterR===$filterEx, '::filterFilter');

        $filterS = [
            'field_name as Field Name: [required:negate=false]',
        ];
        $filterEx = [
            'field_name'=>[
                'name'=>'field_name',
                'alias'=>'Field Name',
                'filter'=>[
                    'required'=>['negate'=>false],
                ],
            ],
        ];
        $filterR = $filter->filterFilter($filterS);
        $test->expect($filterR===$filterEx, '::filterFilter');

        $a = null;
        $test->expect(false===$filter->required($a), 'required');
        $test->expect($filter->required($a, ['negate'=>true]), 'required');

        $a = 'alpha';
        $test->expect($filter->alpha($a), 'alpha true');
        $test->expect($filter->alpha($a.' ', ['space'=>true]), 'alpha true');
        $test->expect(false===$filter->alpha($a, ['max'=>3]), 'alpha max 3 false');
        $test->expect($filter->alpha($a, ['min'=>3]), 'alpha min 3 true');

        $a = 'al123';
        $test->expect($filter->alnum($a), 'alnum');

        $a = '123';
        $test->expect($filter->numeric($a), 'numeric');
        $test->expect($filter->numeric($a.'.00', ['commaperiod'=>true]), 'numeric with commaperiod');

        $a = '2014-10-12';
        $test->expect($filter->dateSQL($a), 'Date SQL valid');
        $test->expect($filter->dateSQL($a, ['convert'=>true])==='12/10/2014', 'Date SQL valid');

        $a = '12/10/2014';
        $test->expect($filter->dateIndo($a), 'Date Indo valid');
        $test->expect($filter->dateIndo($a, ['convert'=>true])==='2014-10-12', 'Date Indo valid');

        $a = 'a';
        $test->expect($filter->inArray($a, ['range'=>range('a','c')]), 'in array valid');

        $a = 'a';
        $test->expect($filter->match($a, ['value'=>'a']), 'match valid');
        $test->expect($filter->match($a, ['pattern'=>'/^a$/']), 'match valid');

        $test->expect($filter->inMap('GJ001', ['class'=>'\\tests\\data\\Gejala','field'=>'kode_gejala']), 'check inMap is valid');
        $test->expect(false===$filter->inMap('GJ100', ['class'=>'\\tests\\data\\Gejala','field'=>'kode_gejala']), 'check inMap is invalid');

        $map = \App::map('gejala');
        $map->load(['nama_gejala=?','Mata nyeri hebat']);
        $test->expect($filter->uniqueMap('Mata tidak nyeri hebat', ['instance'=>$map,'field_id'=>'kode_gejala', 'field'=>'nama_gejala']), 'uniquemap valid');
        $test->expect(false===$filter->uniqueMap('Mata menonjol', ['instance'=>$map,'field'=>'nama_gejala','field_id'=>'kode_gejala']), 'uniquemap invalid');

        $this->testResult($test);
    }
}