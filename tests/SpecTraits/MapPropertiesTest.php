<?php

namespace Tests\Battis\CRUD\SpecTraits;

use Battis\CRUD\Record;
use Tests\Battis\CRUD\fixtures\SpecWithMapPropertiesTrait as Spec;
use Tests\Battis\CRUD\SpecTest;

class MapPropertiesTest extends SpecTest
{
    public function testTraitConstructor()
    {
        foreach (
            [
                [
                    [
                        Record::class,
                        "",
                        "identifier",
                        [
                            Spec::$MAPPING => [
                                "identifier" => "id",
                            ],
                        ],
                    ],
                    ["records", "identifier", ["identifier" => "id"]],
                ],
                [
                    [
                        Record::class,
                        "foo",
                        "bar",
                        [
                            Spec::$MAPPING => [
                                "bar" => "id",
                                "argle" => "bargle",
                            ],
                        ],
                    ],
                    ["foo", "bar", ["bar" => "id", "argle" => "bargle"]],
                ],
            ]
            as $test
        ) {
            $s = new Spec(...$test[0]);
            $this->assertEquals($test[1][0], $s->getTableName());
            $this->assertEquals($test[1][1], $s->getPrimaryKey());
            foreach ($test[1][2] as $property => $field) {
                $this->assertEquals($field, $s->mapPropertyToField($property));
                $this->assertEquals($property, $s->mapFieldToProperty($field));
            }
            $obj = array_combine(
                array_keys($test[1][2]),
                range(1, count($test[1][2]))
            );
            $row = array_combine(
                array_values($test[1][2]),
                range(1, count($test[1][2]))
            );
            $this->assertEquals($obj, $s->mapFieldsToProperties($row));
            $this->assertEquals($row, $s->mapPropertiesToFields($obj));
        }
    }
}
