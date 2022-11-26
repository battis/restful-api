<?php

namespace Battis\CRUD\Tests;

use Battis\CRUD\Connection;
use Battis\CRUD\Exceptions\RecordException;
use Battis\CRUD\Record;
use Battis\CRUD\Tests\Fixtures\RecordTest\RecordFixture;

class RecordTest extends TestCase
{
    protected function setUp(): void
    {
        $this->getPDO()->query(file_get_contents($this->getFixturePath(__FILE__) . '/record_fixtures.sql'));
        parent::setUp();
    }

    public function getDataset()
    {
        return $this->getCsvDataSet(__FILE__, 'record_fixtures');
    }

    private function assertRecordEquals(array $expected, Record $actual)
    {
        list ($id, $field1, $field2, $field3) = $expected;
        $this->assertEquals($id, $actual->id);
        $this->assertEquals($field1, $actual->field1);
        $this->assertEquals($field2, $actual->field2);
        $this->assertEquals($field3, $actual->field3);
    }

    public function testAssign()
    {
        $data = [
            [
                [
                    'id' => 1,
                    'field1' => 'test1',
                    'field2' => 123,
                    'field3' => 456
                ],
                [1, 'test1', 123, 456]
            ], [
                [],
                [null, null, null, null]
            ],
            [
                [
                    'field1' => 'test2',
                    'field3' => 789
                ],
                [null, 'test2', null, 789]
            ]
        ];

        foreach($data as list($arg, list($id, $field1, $field2, $field3))) {
            $record = new RecordFixture($arg);

            if ($id === null) {
                $this->assertObjectNotHasAttribute('id', $record);
            } else {
                $this->assertEquals($id, $record->id);
            }
            $this->assertEquals($field1, $record->field1);
            $this->assertEquals($field2, $record->field2);
            $this->assertEquals($field3, $record->field3);
        }
    }

    public function testCreate()
    {
        $data = [
            [
                [
                    'field1' => 'test1',
                    'field2' => 123,
                    'field3' => 456
                ],
                [3, 'test1', 123, 456]
            ],
            [
                [
                    'field1' => 'TEst 2',
                    'field2' => 987,
                    'field3' => 789
                ],
                [4, 'TEst 2', 987, 789]
            ]
        ];

        $i = 1;
        Connection::setPDO($this->getPDO());
        foreach ($data as list ($arg, $expected)) {

            $record = RecordFixture::create($arg);

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, "record_fixtures-testCreate-$i");
            $this->assertRecordEquals($expected, $record);

            $i++;
        }

        $this->assertNull(RecordFixture::create(['not a field' => 'value']));
        $this->assertTableEqualsCsv('record_fixtures', __FILE__, "record_fixtures-testCreate-2");
    }

    public function testRead()
    {
        $data = [
            1 => [1, 'testRow1', 123, null],
            2 => [2, 'test row 2', 456, null]
        ];

        $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures');

        foreach($data as $id => $expected) {

            $record = RecordFixture::read($id);

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures');
            $this->assertRecordEquals($expected, $record);
        }

        $this->assertNull(RecordFixture::read(-1));
    }

    public function testRetrieve()
    {
        $this->getPDO()->query("INSERT INTO record_fixtures (field1, field2, field3) VALUES ('testRow1', 456, 789)");

        $data = [
            [
                ['field1' => 'testRow1'],
                2,
                [
                    [1, 'testRow1', 123, null],
                    [3, 'testRow1', 456, 789]
                ]
            ],
            [
                ['field2' => 456],
                2,
                [
                    [2, 'test row 2', 456, null],
                    [3, 'testRow1', 456, 789]
                ]
            ],
            [
                ['field3' => -10],
                0,
                []
            ],
            [
                null,
                3,
                [
                    [1, 'testRow1', 123, null],
                    [2, 'test row 2', 456, null],
                    [3, 'testRow1', 456, 789]
                ]
            ]
        ];

        $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures-testRetrieve');

        foreach($data as list($arg, $count, $values)) {

            if ($arg === null) {
                $result = RecordFixture::retrieve();
            } else {
                $result = RecordFixture::retrieve($arg);
            }

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures-testRetrieve');

            $this->assertCount($count, $result);
            for ($i = 0; $i < $count; $i++) {
                $this->assertRecordEquals($values[$i], $result[$i]);
            }
        }
    }

    public function testUpdate()
    {
        $data = [
            1 => [1, 'testRow1', 456, 789],
            2 => [2, 'test row 2', 123, 456]
        ];

        $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures');

        foreach($data as $id => $expected) {
            list($id, $field1, $field2, $field3) = $expected;
            $record = RecordFixture::update([
                'id' => $id,
                'field2' => $field2,
                'field3' => $field3
            ]);

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, "record_fixtures-testUpdate-$id");
            $this->assertRecordEquals($expected, $record);
        }

        $this->assertNull(RecordFixture::update(['no id' => 'cannot update']));
    }

    public function testDelete()
    {
        $data = [
            1 => [1, 'testRow1', 123, null],
            2 => [2, 'test row 2', 456, null]
        ];

        foreach($data as $id => $expected) {
            $record = RecordFixture::delete($id);

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, "record_fixtures-testDelete-$id");
            $this->assertRecordEquals($expected, $record);
        }

        $this->assertNull(RecordFixture::delete(-1));
    }

    public function testSave()
    {
        $data = [
            1 => [1, 'testRow1', 456, 789],
            2 => [2, 'test row 2', 123, 456]
        ];

        $this->assertTableEqualsCsv('record_fixtures', __FILE__, 'record_fixtures');

        foreach($data as $id => $expected) {
            list($id, $field1, $field2, $field3) = $expected;

            $record = RecordFixture::read($id);

            $record->save([
                'field2' => $field2,
                'field3' => $field3
            ]);

            $this->assertTableEqualsCsv('record_fixtures', __FILE__, "record_fixtures-testUpdate-$id");
            $this->assertRecordEquals($expected, $record);
        }

        $record = RecordFixture::read(1);
        $this->getPDO()->query('DELETE FROM record_fixtures WHERE id = 1');
        $this->expectException(RecordException::class);
        $record->save(['field1' => 'fails because backing record deleted']);
    }

    protected function tearDown(): void
    {
        $this->getPDO()->query('DROP TABLE record_fixtures');
    }
}
