<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

/**
 * Arr class tests
 */
class ArrTest extends TestCase
{
    public static function person_provider()
    {
        return [
            [
                [
                    'name'     => 'Jack',
                    'age'      => '21',
                    'weight'   => 200,
                    'location' => [
                        'city'    => 'Pittsburgh',
                        'state'   => 'PA',
                        'country' => 'US',
                    ],
                ],
            ],
        ];
    }

    public static function collection_provider()
    {
        $object          = new \stdClass();
        $object->id      = 7;
        $object->name    = 'Bert';
        $object->surname = 'Visser';

        return [
            [
                [
                    [
                        'id'      => 2,
                        'name'    => 'Bill',
                        'surname' => 'Cosby',
                    ],
                    [
                        'id'      => 5,
                        'name'    => 'Chris',
                        'surname' => 'Rock',
                    ],
                    $object,
                ],
            ],
        ];
    }

    public static function sort_provider()
    {
        return [
            [
                // Unsorted Array
                [
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'dog',
                            ],
                        ],
                    ],
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'fish',
                            ],
                        ],
                    ],
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'cat',
                            ],
                        ],
                    ],
                ],

                // Sorted Array
                [
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'cat',
                            ],
                        ],
                    ],
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'dog',
                            ],
                        ],
                    ],
                    [
                        'info' => [
                            'pet' => [
                                'type' => 'fish',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test Arr::pluck()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider collection_provider
     *
     * @param mixed $collection
     */
    public function test_pluck($collection) : void
    {
        $output   = Arr::pluck($collection, 'id');
        $expected = [2, 5, 7];
        $this->assertSame($expected, $output);
    }

    /**
     * Test Arr::pluck()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider collection_provider
     *
     * @param mixed $collection
     */
    public function test_pluck_with_index($collection) : void
    {
        $output   = Arr::pluck($collection, 'name', 'id');
        $expected = [2 => 'Bill', 5 => 'Chris', 7 => 'Bert'];

        $this->assertSame($expected, $output);
    }

    /**
     * Test Arr::multisort()
     *
     * @covers Velocite\Arr
     *
     */
    public function test_multisort()
    {
        // Test sorting with a single condition
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Mary', 'age' => 25],
            ['name' => 'Bob', 'age' => 40],
        ];
        $conditions = ['age' => SORT_ASC];
        $expected   = [
            ['name' => 'Mary', 'age' => 25],
            ['name' => 'John', 'age' => 30],
            ['name' => 'Bob', 'age' => 40],
        ];

        $result = Arr::multisort($array, $conditions);
        $this->assertSame($expected, $result);
    }

    /**
     * Test Arr::multisort()
     *
     * @covers Velocite\Arr
     *
     */
    public function test_multisort_multiple_conditions()
    {
        // Test sorting with multiple conditions
        $array = [
            ['name' => 'John', 'age' => 30, 'country' => 'USA'],
            ['name' => 'Mary', 'age' => 25, 'country' => 'Canada'],
            ['name' => 'Bob', 'age' => 40, 'country' => 'USA'],
            ['name' => 'Alice', 'age' => 30, 'country' => 'Canada'],
        ];
        $conditions = [
            'age'     => SORT_ASC,
            'country' => SORT_DESC,
            'name'    => SORT_ASC,
        ];
        $expected = [
            ['name' => 'Mary', 'age' => 25, 'country' => 'Canada'],
            ['name' => 'John', 'age' => 30, 'country' => 'USA'],
            ['name' => 'Alice', 'age' => 30, 'country' => 'Canada'],
            ['name' => 'Bob', 'age' => 40, 'country' => 'USA'],
        ];
        $result = Arr::multisort($array, $conditions);

        $this->assertSame($expected, $result);
    }

    /**
     * Test Arr::multisort()
     *
     * @covers Velocite\Arr
     *
     */
    public function test_multisort_deep_sort()
    {
        // Test sorting with deep sorting support
        $array = [
            ['name' => 'John', 'age' => 30, 'country' => ['code' => 'US', 'name' => 'USA']],
            ['name' => 'Mary', 'age' => 25, 'country' => ['code' => 'CA', 'name' => 'Canada']],
            ['name' => 'Bob', 'age' => 40, 'country' => ['code' => 'US', 'name' => 'USA']],
            ['name' => 'Alice', 'age' => 30, 'country' => ['code' => 'CA', 'name' => 'Canada']],
        ];
        $conditions = [
            'country.code' => SORT_ASC,
            'country.name' => SORT_DESC,
        ];
        $expected = [
            ['name' => 'Alice', 'age' => 30, 'country' => ['code' => 'CA', 'name' => 'Canada']],
            ['name' => 'Mary', 'age' => 25, 'country' => ['code' => 'CA', 'name' => 'Canada']],
            ['name' => 'Bob', 'age' => 40, 'country' => ['code' => 'US', 'name' => 'USA']],
            ['name' => 'John', 'age' => 30, 'country' => ['code' => 'US', 'name' => 'USA']],
        ];
        $result = Arr::multisort($array, $conditions);

        $this->assertSame($expected, $result);
    }

    /**
     * Test Arr::multisort()
     *
     * @covers Velocite\Arr
     *
     */
    public function test_multisort_case_insensitive()
    {

        // Test sorting case-insensitively
        $array = [
            ['name' => 'john', 'age' => 30],
            ['name' => 'Mary', 'age' => 25],
            ['name' => 'bob', 'age' => 40],
        ];
        $conditions = ['name' => SORT_ASC];
        $expected = [
            ['name' => 'bob', 'age' => 40],
            ['name' => 'john', 'age' => 30],
            ['name' => 'Mary', 'age' => 25],
        ];
        $result = Arr::multisort($array, $conditions, true);
        $this->assertSame($expected, $result);
    }

    /**
     * Tests Arr::assoc_to_keyval()
     *
     * @covers Velocite\Arr
     */
    public function test_assoc_to_keyval() : void
    {
        $assoc = [
            [
                'color' => 'red',
                'rank'  => 4,
                'name'  => 'Apple',
            ],
            [
                'color' => 'yellow',
                'rank'  => 3,
                'name'  => 'Banana',
            ],
            [
                'color' => 'purple',
                'rank'  => 2,
                'name'  => 'Grape',
            ],
        ];

        $expected = [
            'red'    => 'Apple',
            'yellow' => 'Banana',
            'purple' => 'Grape',
        ];
        $output = Arr::assoc_to_keyval($assoc, 'color', 'name');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::keyval_to_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_keyval_to_assoc() : void
    {
        $keyval = [
            'red'    => 'Apple',
            'yellow' => 'Banana',
            'purple' => 'Grape',
        ];

        $expected = [
            [
                'color' => 'red',
                'name'  => 'Apple',
            ],
            [
                'color' => 'yellow',
                'name'  => 'Banana',
            ],
            [
                'color' => 'purple',
                'name'  => 'Grape',
            ],
        ];

        $output = Arr::keyval_to_assoc($keyval, 'color', 'name');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::key_exists()
     *
     * @dataProvider person_provider
     *
     * @covers Velocite\Arr
     *
     * @param mixed $person
     */
    public function test_key_exists_with_key_found($person) : void
    {
        $expected = true;
        $output   = Arr::key_exists($person, 'name');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::key_exists()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_key_exists_with_key_not_found($person) : void
    {
        $expected = false;
        $output   = Arr::key_exists($person, 'unknown');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::key_exists()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_key_exists_with_dot_separated_key($person) : void
    {
        $expected = true;
        $output   = Arr::key_exists($person, 'location.city');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_key_exists_when_array_is_not_array_or_object() : void
    {
        $foo = ['bar', 'baz'];
        $this->assertFalse(Arr::key_exists($foo, 1));
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_key_exists_when_wrong_key_type() : void
    {
        $this->expectException('\TypeError');
        Arr::key_exists(1);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_key_exists_when_array_is_not_array_access() : void
    {
        $this->expectException('\InvalidArgumentException');
        Arr::key_exists((new \StdClass()), null);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_with_element_found($person) : void
    {
        $expected = 'Jack';
        $output   = Arr::get($person, 'name', 'Unknown Name');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_with_element_not_found($person) : void
    {
        $expected = 'Unknown job';
        $output   = Arr::get($person, 'job', 'Unknown job');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_with_dot_separated_key($person) : void
    {
        $expected = 'Pittsburgh';
        $output   = Arr::get($person, 'location.city', 'Unknown City');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_when_dot_notated_key_is_not_array($person) : void
    {
        $expected = 'Unknown Name';
        $output   = Arr::get($person, 'foo.first', 'Unknown Name');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_with_all_elements_found($person) : void
    {
        $expected = [
            'name'   => 'Jack',
            'weight' => 200,
        ];
        $output = Arr::get($person, ['name', 'weight'], 'Unknown');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_with_all_elements_not_found($person) : void
    {
        $expected = [
            'name'   => 'Jack',
            'height' => 'Unknown',
        ];
        $output = Arr::get($person, ['name', 'height'], 'Unknown');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider person_provider
     *
     * @param mixed $person
     */
    public function test_get_when_keys_is_not_an_array($person) : void
    {
        $expected = 'Jack';
        $output   = Arr::get($person, 'name', 'Unknown');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_get_when_array_is_an_object() : void
    {
        $obj        = new ObjectA();
        $obj['foo'] = 'bar';

        $this->assertSame('bar', Arr::get($obj, 'foo', 'Unknown'));
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     */
    public function test_get_when_array_is_an_object_not_found() : void
    {
        $obj        = new ObjectA();
        $obj['foo'] = 'bar';

        $this->assertSame('Unknown', Arr::get($obj, 'bar', 'Unknown'));
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_get_when_array_is_not_array_or_object() : void
    {
        $this->expectException('\TypeError');
        Arr::get(1);
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_get_when_array_is_not_array_access() : void
    {
        $this->expectException('\InvalidArgumentException');
        Arr::get((new \StdClass()));
    }

    /**
     * Tests Arr::get()
     *
     * @covers Velocite\Arr
     */
    public function test_get_with_null_key() : void
    {
        $numbers = ['one' => 1, 'two' => 2];
        $this->assertSame($numbers, Arr::get($numbers));
    }

    /**
     * Tests Arr::flatten()
     *
     * @covers Velocite\Arr
     */
    public function test_flatten() : void
    {
        $indexed = [ ['a'], ['b'], ['c'] ];

        $expected = [
            '0_0' => 'a',
            '1_0' => 'b',
            '2_0' => 'c',
        ];

        $output = Arr::flatten($indexed, '_');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::flatten_assoc()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Arr
     */
    public function test_flatten_assoc() : void
    {
        $people = [
            [
                'name' => 'Jack',
                'age'  => 21,
            ],
            [
                'name' => 'Jill',
                'age'  => 23,
            ],
        ];

        $expected = [
            '0:name' => 'Jack',
            '0:age'  => 21,
            '1:name' => 'Jill',
            '1:age'  => 23,
        ];

        $output = Arr::flatten_assoc($people);
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::flatten_assoc() with recursive arrays
     *
     * @covers Velocite\Arr
     */
    public function test_flatten_recursive_index() : void
    {
        $people = [
            [
                'name'     => 'Jack',
                'age'      => 21,
                'children' => [
                    [
                        'name' => 'Johnny',
                        'age'  => 4,
                    ],
                    [
                        'name' => 'Jimmy',
                        'age'  => 3,
                    ],
                ],
            ],
            [
                'name' => 'Jill',
                'age'  => 23,
            ],
        ];

        $expected = [
            '0:name'            => 'Jack',
            '0:age'             => 21,
            '0:children:0:name' => 'Johnny',
            '0:children:0:age'  => 4,
            '0:children:1:name' => 'Jimmy',
            '0:children:1:age'  => 3,
            '1:name'            => 'Jill',
            '1:age'             => 23,
        ];

        $output = Arr::flatten($people, ':');
        $this->assertSame($expected, $output);
    }



    /**
     * Tests Arr::insert()
     *
     * @covers Velocite\Arr
     */
    public function test_insert() : void
    {
        $people = ['Jack', 'Jill'];

        $expected = ['Humpty', 'Jack', 'Jill'];
        $output   = Arr::insert($people, 'Humpty', 0);

        $this->assertSame(true, $output);
        $this->assertSame($expected, $people);
    }

    /**
     * Tests Arr::insert()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_with_index_out_of_range() : void
    {
        $people = ['Jack', 'Jill'];

        $output = Arr::insert($people, 'Humpty', 4);

        $this->assertFalse($output);
    }

    /**
     * Tests Arr::merge()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_returns_expected_result()
    {
        $array1 = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => ['value3', 'value4'],
        ];

        $array2 = [
            'key2' => 'new value',
            'key3' => ['value5', 'value6'],
            'key4' => 'value7',
        ];

        $array3 = [
            'key1' => 'override value1',
            'key3' => ['value8', 'value9'],
            'key5' => 'value10',
        ];

        $result = Arr::merge($array1, $array2, $array3);

        $expectedResult = [
            'key1' => 'override value1',
            'key2' => 'new value',
            'key3' => ['value3', 'value4', 'value5', 'value6', 'value8', 'value9'],
            'key4' => 'value7',
            'key5' => 'value10',
        ];

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests Arr::merge()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_throws_invalid_argument_exception_when_first_argument_is_not_array()
    {
        $this->expectException(\InvalidArgumentException::class);

        Arr::merge('not an array', []);
    }

    /**
     * Tests Arr::merge()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_throws_invalid_argument_exception_when_arguments_are_not_arrays()
    {
        $this->expectException(\InvalidArgumentException::class);

        Arr::merge([], 'not an array');
    }

    /**
     * Tests Arr::merge()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_works_when_called_with_a_single_array()
    {
        $array1 = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => ['value3', 'value4'],
        ];

        $result = Arr::merge($array1);

        $this->assertSame($array1, $result);
    }

    /**
     * Tests Arr::merge()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_works_when_called_with_numeric_keys()
    {
        $array1 = [1, 2, 3];

        $array2 = [4, 5, 6];

        $result = Arr::merge($array1, $array2);

        $this->assertSame([1, 2, 3, 4, 5, 6], $result);
    }

    /**
     * Tests Arr::merge_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_assoc(): void
    {
        // Test merging 2 arrays
        $array1 = ['a' => 1, 'b' => ['c' => 2]];
        $array2 = ['b' => ['d' => 3], 'e' => 4];
        $expected = ['a' => 1, 'b' => ['c' => 2, 'd' => 3], 'e' => 4];
        $this->assertSame($expected, Arr::merge_assoc($array1, $array2));

        // Test merging 3 arrays
        $array1 = ['a' => 1, 'b' => ['c' => 2]];
        $array2 = ['b' => ['d' => 3], 'e' => 4];
        $array3 = ['a' => ['f' => 5], 'g' => 6];
        $expected = ['a' => ['f' => 5], 'b' => ['c' => 2, 'd' => 3], 'e' => 4, 'g' => 6];
        $this->assertSame($expected, Arr::merge_assoc($array1, $array2, $array3));

        // Test merging 1 array
        $array1 = ['a' => 1, 'b' => ['c' => 2]];
        $expected = ['a' => 1, 'b' => ['c' => 2]];
        $this->assertSame($expected, Arr::merge_assoc($array1));
    }

    /**
     * Tests Arr::merge_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_assoc_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arr::merge_assoc('not an array');
    }

    /**
     * Tests Arr::merge_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_merge_assoc_throws_exception_param_2(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Arr::merge_assoc([], 'not an array');
    }

    /**
     * Tests Arr::insert_after_key()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_before_key_that_exists() : void
    {
        $people = ['first' => 'Jack', 'second' => 'Jill'];
        $people_idx = array_values(['first' => 'Jack', 'second' => 'Jill']);

        $expected = ['first' => 'Jack', 'Humpty', 'second' => 'Jill'];
        $expected_idx = ['Jack', 'Humpty', 'Jill'];
        $output_idx   = Arr::insert_before_key($people_idx, ['Humpty'], 1);
        $output  = Arr::insert_before_key($people, ['Humpty'], 'second');
        $output  = Arr::insert_before_key($people, ['Humpty'], 'second');

        $this->assertTrue($output);
        $this->assertTrue($output_idx);
        $this->assertSame($expected, $people);
        $this->assertSame($expected_idx, $people_idx);
    }

    /**
     * Tests Arr::insert_before_key()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_before_key_that_does_not_exist() : void
    {
        $people = ['Jack', 'Jill'];
        $output = Arr::insert_before_key($people, 'Humpty', 6);
        $this->assertFalse($output);
    }

    /**
     * Tests Arr::insert_after_key()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_after_key_that_exists() : void
    {
        $people = ['Jack', 'Jill'];

        $expected = ['Jack', 'Jill', 'Humpty'];
        $output   = Arr::insert_after_key($people, 'Humpty', 1);

        $this->assertTrue($output);
        $this->assertSame($expected, $people);
    }

    /**
     * Tests Arr::insert_after_key()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_after_key_that_does_not_exist() : void
    {
        $people = ['Jack', 'Jill'];
        $output = Arr::insert_after_key($people, 'Humpty', 6);
        $this->assertFalse($output);
    }

    /**
     * Tests Arr::insert_before_value()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_before_value_that_exists() : void
    {
        $people   = ['Jack', 'Jill'];
        $expected = ['Humpty', 'Jack', 'Jill'];
        $output   = Arr::insert_before_value($people, 'Humpty', 'Jack');
        $this->assertTrue($output);
        $this->assertSame($expected, $people);
    }

    /**
     * Tests Arr::insert_before_value()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_before_value_that_does_not_exists() : void
    {
        $people = ['Jack', 'Jill'];
        $output = Arr::insert_before_value($people, 'Humpty', 'Joe');
        $this->assertFalse($output);
    }

    /**
     * Tests Arr::insert_after_value()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_after_value_that_exists() : void
    {
        $people   = ['Jack', 'Jill'];
        $expected = ['Jack', 'Humpty', 'Jill'];
        $output   = Arr::insert_after_value($people, 'Humpty', 'Jack');
        $this->assertTrue($output);
        $this->assertSame($expected, $people);
    }

    /**
     * Tests Arr::insert_after_value()
     *
     * @covers Velocite\Arr
     */
    public function test_insert_after_value_that_does_not_exists() : void
    {
        $people = ['Jack', 'Jill'];
        $output = Arr::insert_after_value($people, 'Humpty', 'Joe');
        $this->assertFalse($output);
    }

    /**
     * Tests Arr::average()
     *
     * @covers Velocite\Arr
     */
    public function test_average() : void
    {
        $arr = [13, 8, 6];
        $this->assertSame(9.0, Arr::average($arr));
    }

    /**
     * Tests Arr::average()
     *
     * @covers Velocite\Arr
     */
    public function test_average_of_empty_array() : void
    {
        $arr = [];
        $this->assertSame(0.0, Arr::average($arr));
    }

    /**
     * Tests Arr::filter_prefixed()
     *
     * @covers Velocite\Arr
     */
    public function test_filter_prefixed() : void
    {
        $arr = ['foo' => 'baz', 'prefix_bar' => 'yay'];

        $output = Arr::filter_prefixed($arr, 'prefix_');
        $this->assertSame(['bar' => 'yay'], $output);
    }

    /**
     * Tests Arr::sort()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider sort_provider
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function test_sort_asc($data, $expected) : void
    {
        $this->assertSame($expected, Arr::sort($data, 'info.pet.type', 'asc'));
    }

    /**
     * Tests Arr::sort()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider sort_provider
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function test_sort_desc($data, $expected) : void
    {
        $expected = array_reverse($expected);
        $this->assertSame($expected, Arr::sort($data, 'info.pet.type', 'desc'));
    }

    /**
     * Tests Arr::sort()
     *
     * @covers Velocite\Arr
     *
     * @dataProvider sort_provider
     *
     * @param array $data
     * @param array $expected
     */
    public function test_sort_invalid_direction(array $data, array $expected) : void
    {
        $this->expectException('InvalidArgumentException');
        Arr::sort($data, 'info.pet.type', 'notavaliddirection');
    }

    /**
     * Test sort with empty array
     *
     * @covers Velocite\Arr
     *
     * @return void
     */
    public function test_sort_empty() : void
    {
        $expected = [];
        $output   = Arr::sort([], 'test', 'test');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::filter_keys()
     *
     * @covers Velocite\Arr
     */
    public function test_filter_keys() : void
    {
        $data = [
            'epic' => 'win',
            'weak' => 'sauce',
            'foo'  => 'bar',
        ];
        $expected = [
            'epic' => 'win',
            'foo'  => 'bar',
        ];
        $expected_remove = [
            'weak' => 'sauce',
        ];
        $keys = ['epic', 'foo'];
        $this->assertSame($expected, Arr::filter_keys($data, $keys));
        $this->assertSame($expected_remove, Arr::filter_keys($data, $keys, true));
    }

    /**
     * Tests Arr::to_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_to_assoc_with_even_number_of_elements() : void
    {
        $arr      = ['foo', 'bar', 'baz', 'yay'];
        $expected = ['foo' => 'bar', 'baz' => 'yay'];
        $this->assertSame($expected, Arr::to_assoc($arr));
    }

    /**
     * Tests Arr::to_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_to_assoc_with_odd_number_of_elements() : void
    {
        $this->expectException('BadMethodCallException');
        $arr = ['foo', 'bar', 'baz'];
        Arr::to_assoc($arr);
    }

    /**
     * Tests Arr::prepend()
     *
     * @covers Velocite\Arr
     */
    public function test_prepend() : void
    {
        $arr = [
            'two'   => 2,
            'three' => 3,
        ];
        $expected = [
            'one'   => 1,
            'two'   => 2,
            'three' => 3,
        ];
        Arr::prepend($arr, 'one', 1);
        $this->assertSame($expected, $arr);
    }

    /**
     * Tests Arr::prepend()
     *
     * @covers Velocite\Arr
     */
    public function test_prepend_array() : void
    {
        $arr = [
            'two'   => 2,
            'three' => 3,
        ];
        $expected = [
            'one'   => 1,
            'two'   => 2,
            'three' => 3,
        ];
        Arr::prepend($arr, ['one' => 1]);
        $this->assertSame($expected, $arr);
    }

    /**
     * Tests Arr::is_multi()
     *
     * @covers Velocite\Arr
     */
    public function test_multidimensional_array() : void
    {
        // Single array
        $arr_single = ['one' => 1, 'two' => 2];
        $this->assertFalse(Arr::is_multi($arr_single));

        // Multi-dimensional array
        $arr_multi = ['one' => ['test' => 1], 'two' => ['test' => 2], 'three' => ['test' => 3]];
        $this->assertTrue(Arr::is_multi($arr_multi));

        // Multi-dimensional array (not all elements are arrays)
        $arr_multi_strange = ['one' => ['test' => 1], 'two' => ['test' => 2], 'three' => 3];
        $this->assertTrue(Arr::is_multi($arr_multi_strange, false));
        $this->assertFalse(Arr::is_multi($arr_multi_strange, true));
    }

    /**
     * Tests Arr::search()
     *
     * @covers Velocite\Arr
     */
    public function test_search_single_array() : void
    {
        // Single array
        $arr_single = ['one' => 1, 'two' => 2];
        $expected   = 'one';
        $this->assertSame($expected, Arr::search($arr_single, 1));

        // Default
        $expected = null;
        $this->assertSame($expected, Arr::search($arr_single, 3));
        $expected = 'three';
        $this->assertSame($expected, Arr::search($arr_single, 3, 'three'));

        // Single array (int key)
        $arr_single = [0 => 'zero', 'one' => 1, 'two' => 2];
        $expected   = null;
        $this->assertSame($expected, Arr::search($arr_single, 0));

        $this->expectException(\InvalidArgumentException::class);
        Arr::search('foo', 0);
    }

    /**
     * Tests Arr::search()
     *
     *
     * @covers Velocite\Arr
     */
    public function test_search_multi_array() : void
    {
        // Multi-dimensional array
        $arr_multi = ['one' => ['test' => 1], 'two' => ['test' => 2], 'three' => ['test' => ['a' => 'a', 'b' => 'b']]];
        $expected  = 'one';
        $this->assertSame($expected, Arr::search($arr_multi, ['test' => 1], null, false));
        $expected = null;
        $this->assertSame($expected, Arr::search($arr_multi, 1, null, false));

        // Multi-dimensional array (recursive)
        $expected = 'one.test';
        $this->assertSame($expected, Arr::search($arr_multi, 1));

        $expected = 'three.test.b';
        $this->assertSame($expected, Arr::search($arr_multi, 'b', null, true));
    }

    /**
     * Tests Arr::sum()
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     */
    public function test_sum_multi_array() : void
    {
        $arr_multi = [
            [
                'name'   => 'foo',
                'scores' => [
                    'sports' => 5,
                    'math'   => 20,
                ],
            ],
            [
                'name'   => 'bar',
                'scores' => [
                    'sports' => 7,
                    'math'   => 15,
                ],
            ],
            [
                'name'   => 'fuel',
                'scores' => [
                    'sports' => 8,
                    'math'   => 5,
                ],
            ],
            [
                'name'   => 'php',
                'scores' => [
                    'math' => 10,
                ],
            ],
        ];

        $expected = 50.0;
        $test     = Arr::sum($arr_multi, 'scores.math');
        $this->assertSame($expected, $test);

        $expected = 20.0;
        $test     = Arr::sum($arr_multi, 'scores.sports');
        $this->assertSame($expected, $test);

        // test invalid parameter
        $this->expectException(\InvalidArgumentException::class);
        Arr::sum('foo', 'bar');
    }

    /**
     * Tests Arr::test_previous_by_key_previous_key()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_key(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->assertSame('a', Arr::previous_by_key($array, 'b', false, false));
        $this->assertSame(1, Arr::previous_by_key($array, 'b', true, false));
        $this->assertSame(null, Arr::previous_by_key($array, 'a', false, false));
        $this->assertFalse(Arr::previous_by_key($array, 'd', false, false));
        $this->assertFalse(Arr::previous_by_key([], 'd', false, false));
        
        $this->expectException(\InvalidArgumentException::class);
        Arr::previous_by_key('not an array', 'key');
    }

    /**
     * Tests Arr::test_next_by_key_not_found()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_not_found() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => 'B', 6 => 'C'];

        // test: key not found in array
        $expected = false;
        $test     = Arr::next_by_key($arr, 1);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_key_no_next_key()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_no_next_key() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => 'B', 6 => 'C'];

        // test: no next key
        $expected = null;
        $test     = Arr::next_by_key($arr, 6);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_key_strict_comp()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_strict_comp() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => 'B', 6 => 'C'];

        // test: strict key comparison
        $expected = false;
        $test     = Arr::next_by_key($arr, '6', false, true);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_key_get_next_key_value()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_get_next_key_value() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => 'B', 6 => 'C'];

        // test: get next key
        $expected = 6;
        $test     = Arr::next_by_key($arr, 4);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_key_get_next_key()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_get_next_key() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => 'B', 6 => 'C'];

        // test: get next value
        $expected = 'C';
        $test     = Arr::next_by_key($arr, 4, true);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_key_get_next_key()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_key_invalid() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        Arr::next_by_key('foo', 4, true);
    }

    /**
     * Tests Arr::test_previous_by_value_not_found()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_value_not_found() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => '2', 6 => 'C'];

        // test: value not found in array
        $expected = false;
        $test     = Arr::previous_by_value($arr, 'Z');
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_previous_by_value_no_previous()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_value_no_previous() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => '2', 6 => 'C'];

        // test: no previous value
        $expected = null;
        $test     = Arr::previous_by_value($arr, 'A');
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_previous_by_value_strict()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_value_strict() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => '2', 6 => 'C'];

        // test: strict value comparison
        $expected = false;
        $test     = Arr::previous_by_value($arr, 2, true, true);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_previous_by_value_get_previous()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_value_get_previous() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => '2', 6 => 'C'];

        // test: get previous value
        $expected = 'A';
        $test     = Arr::previous_by_value($arr, '2');
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_previous_by_value_get_previous_key()
     *
     * @covers Velocite\Arr
     */
    public function test_previous_by_value_get_previous_key() : void
    {
        // our test array
        $arr = [2 => 'A', 4 => '2', 6 => 'C'];

        // test: get previous key
        $expected = 4;
        $test     = Arr::previous_by_value($arr, 'C', false);
        $this->assertTrue($expected === $test);
    }

    /**
     * Tests Arr::test_next_by_value_get_next()
     *
     * @covers Velocite\Arr
     */
    public function test_next_by_value()
    {
        // test basic array with string values
        $array = ['foo', 'bar', 'baz'];
        $this->assertSame('bar', Arr::next_by_value($array, 'foo'));
        $this->assertSame('baz', Arr::next_by_value($array, 'bar'));
        $this->assertNull(Arr::next_by_value($array, 'baz'));
        $this->assertFalse(Arr::next_by_value($array, 'qux'));
        // test array with mixed values
        $array = ['foo' => 1, 'bar' => '2', 'baz' => true, 'qux' => false, 'quux' => null];
        $this->assertSame('2', Arr::next_by_value($array, 1, true));
        $this->assertTrue(Arr::next_by_value($array, '2', true));
        $this->assertFalse(Arr::next_by_value($array, true, true, true));
        $this->assertNull(Arr::next_by_value($array, null, true));
        $this->assertNull(Arr::next_by_value($array, false, true));

        // test array with numeric keys and strict comparison
        $array = [1 => 'foo', 2 => 'bar', 3 => 'baz'];
        $this->assertSame('bar', Arr::next_by_value($array, 'foo', true, true));
        $this->assertSame('baz', Arr::next_by_value($array, 'bar', true, true));
        $this->assertNull(Arr::next_by_value($array, 'baz', true, true));
        $this->assertFalse(Arr::next_by_value($array, 'qux', true, true));

        // test invalid parameter
        $this->expectException(\InvalidArgumentException::class);
        Arr::next_by_value('foo', 'bar');
    }

    /**
     * Tests Arr::subset()
     *
     * @dataProvider person_provider
     *
     * @covers Velocite\Arr
     *
     * @param mixed $person
     */
    public function test_subset_basic_usage($person) : void
    {
        $expected = [
            'name'     => 'Jack',
            'location' => [
                'city'    => 'Pittsburgh',
                'state'   => 'PA',
                'country' => 'US',
            ],
        ];

        $got = Arr::subset($person, ['name', 'location']);
        $this->assertSame($expected, $got);

        $expected = [
            'name'     => 'Jack',
            'location' => [
                'country' => 'US',
            ],
        ];

        $got = Arr::subset($person, ['name', 'location.country']);
        $this->assertSame($expected, $got);
    }

    /**
     * Tests Arr::subset()
     *
     * @dataProvider person_provider
     *
     * @covers Velocite\Arr
     * @covers Velocite\Str
     *
     * @param mixed $person
     */
    public function test_subset_missing_items($person) : void
    {
        $expected = [
            'name'     => 'Jack',
            'location' => [
                'street'  => null,
                'country' => 'US',
            ],
            'occupation' => null,
        ];

        $got = Arr::subset($person, ['name', 'location.street', 'location.country', 'occupation']);
        $this->assertSame($expected, $got);

        $expected = [
            'name'     => 'Jack',
            'location' => [
                'street'  => 'Unknown',
                'country' => 'US',
            ],
            'occupation' => 'Unknown',
        ];

        $got = Arr::subset($person, ['name', 'location.street', 'location.country', 'occupation'], 'Unknown');
        $this->assertSame($expected, $got);
    }

    /**
     * Tests Arr::filter_recursive()
     *
     * @covers Velocite\Arr
     */
    public function test_filter_recursive() : void
    {
        $arr = [
            'user_name'    => 'John',
            'user_surname' => 'Lastname',
            'info'         => [
                0 => [
                    'data' => 'a value',
                ],
                1 => [
                    'data' => '',
                ],
                2 => [
                    'data' => 0,
                ],
            ],
        ];

        $expected = [
            'user_name'    => 'John',
            'user_surname' => 'Lastname',
            'info'         => [
                0 => [
                    'data' => 'a value',
                ],
            ],
        ];
        $got = Arr::filter_recursive($arr);
        $this->assertSame($expected, $got);

        $expected = [
            'user_name'    => 'John',
            'user_surname' => 'Lastname',
            'info'         => [
                0 => [
                    'data' => 'a value',
                ],
                1 => [
                ],
                2 => [
                    'data' => 0,
                ],
            ],
        ];
        $got = Arr::filter_recursive(
            $arr,
            static function($item) { return $item !== ''; }
        );
        $this->assertSame($expected, $got);
    }

    /**
     * Tests Arr::set()
     *
     * @covers Velocite\Arr
     */
    public function test_set_null_key() : void
    {
        $target = ['foo' => 'bar'];
        $numbers = ['one' => 1, 'two' => 2];
        $this->assertNull(Arr::set($target, null, $numbers));
        $this->assertSame($target, $numbers);
    }

    /**
     * Tests Arr::set()
     *
     * @covers Velocite\Arr
     */
    public function test_set_with_key_array() : void
    {
        $numbers = ['one' => 1, 'two' => 2];
        Arr::set($numbers, ['one' => 3, 'two' => 3]);
        $this->assertSame(3, $numbers['one']);
        $this->assertSame(3, $numbers['two']);
    }

    /**
     * Tests Arr::delete()
     *
     * @covers Velocite\Arr
     */
    public function test_delete() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3];
        $expected = ['one' => 1, 'three' => 3];
        Arr::delete($numbers, 'two');
        $this->assertSame($expected, $numbers);
    }

    /**
     * Tests Arr::delete()
     *
     * @covers Velocite\Arr
     */
    public function test_delete_key_array() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3];
        $expected = ['one' => 1];
        Arr::delete($numbers, ['two', 'three']);
        $this->assertSame($expected, $numbers);
    }

    /**
     * Tests Arr::delete()
     *
     * @covers Velocite\Arr
     */
    public function test_delete_key_null() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertFalse(Arr::delete($numbers, null));
    }

    /**
     * Tests Arr::delete()
     *
     * @covers Velocite\Arr
     */
    public function test_delete_key_not_exists() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertFalse(Arr::delete($numbers, 'four'));
    }

    /**
     * Tests Arr::delete()
     *
     * @covers Velocite\Arr
     */
    public function test_delete_key__dot_notation() : void
    {
        $numbers = ['one' => [ 'two' => 2, 'three' => 3]];
        $expected = ['one' => [ 'three' => 3]];
        $this->assertTrue(Arr::delete($numbers, 'one.two'));
        $this->assertSame($expected, $numbers);
    }

    /**
     * Tests Arr::is_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_is_assoc() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertTrue(Arr::is_assoc($numbers));
    }

    /**
     * Tests Arr::is_assoc()
     *
     * @covers Velocite\Arr
     */
    public function test_is_not_assoc() : void
    {
        $numbers = ['one', 'two', 'three'];
        $this->assertFalse(Arr::is_assoc($numbers));
    }

    /**
     * Tests Arr::reverse_flatten()
     *
     * @covers Velocite\Arr
     */
    public function test_reverse_flatten() : void
    {

        $expected= [ ['a'], ['b'], ['c'] ];

        $indexed  = [
            '0_0' => 'a',
            '1_0' => 'b',
            '2_0' => 'c',
        ];

        $output = Arr::reverse_flatten($indexed, '_');
        $this->assertSame($expected, $output);
    }

    /**
     * Tests Arr::remove_prefixed()
     *
     * @covers Velocite\Arr
     */
    public function test_remove_prefixed() : void
    {
        $numbers = ['one' => 1, '1$_two' => 2, 'three' => 3, '1$_four' => 4];
        $expected = ['one' => 1, 'three' => 3];

        $this->assertSame($expected, Arr::remove_prefixed($numbers, '1$_'));
    }

    /**
     * Tests Arr::remove_suffixed()
     *
     * @covers Velocite\Arr
     */
    public function test_remove_suffixed() : void
    {
        $numbers = ['one' => 1, 'two__1$' => 2, 'three' => 3, 'four__1$' => 4];
        $expected = ['one' => 1, 'three' => 3];

        $this->assertSame($expected, Arr::remove_suffixed($numbers, '__1$'));
    }

    /**
     * Tests Arr::filter_suffixed()
     *
     * @covers Velocite\Arr
     */
    public function test_filter_suffixed() : void
    {
        $numbers = ['one' => 1, 'two__1$' => 2, 'three' => 3, 'four__1$' => 4];
        $expected = [ 'two__1$' => 2, 'four__1$' => 4];
        $expected_remove_suffix = [ 'two' => 2, 'four' => 4];

        $this->assertSame($expected, Arr::filter_suffixed($numbers, '__1$', false));
        $this->assertSame($expected_remove_suffix, Arr::filter_suffixed($numbers, '__1$'));
    }

    /**
     * Tests Arr::replace_key()
     *
     * @covers Velocite\Arr
     */
    public function test_replace_key() : void
    {
        $numbers = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4];
        $expected = ['zero' => 1, 'two' => 2, 'three' => 3, 'five' => 4];
        $expected_str = ['one' => 1, 'two' => 2, 'three' => 3, 'five' => 4];

        $this->assertSame($expected, Arr::replace_key($numbers, ['one' => 'zero', 'four' => 'five']));
        $this->assertSame($expected_str, Arr::replace_key($numbers, 'four', 'five'));

        $this->expectException('\InvalidArgumentException');
        Arr::replace_key($numbers, 1);
    }

    /**
     * Tests Arr::test_in_array_recursive()
     *
     * @covers Velocite\Arr
     */
    public function test_in_array_recursive()
    {
        // Test searching for a value in a flat array
        $haystack = ['foo', 'bar', 'baz'];
        $this->assertTrue(Arr::in_array_recursive('foo', $haystack));
        $this->assertTrue(Arr::in_array_recursive('bar', $haystack));
        $this->assertTrue(Arr::in_array_recursive('baz', $haystack));
        $this->assertFalse(Arr::in_array_recursive('qux', $haystack));

        // Test searching for a value in a nested array
        $haystack = [
            'foo',
            'bar',
            [
                'baz',
                'qux',
                [
                    'corge',
                    'grault',
                ],
            ],
        ];
        $this->assertTrue(Arr::in_array_recursive('foo', $haystack));
        $this->assertTrue(Arr::in_array_recursive('bar', $haystack));
        $this->assertTrue(Arr::in_array_recursive('baz', $haystack));
        $this->assertTrue(Arr::in_array_recursive('qux', $haystack));
        $this->assertTrue(Arr::in_array_recursive('corge', $haystack));
        $this->assertTrue(Arr::in_array_recursive('grault', $haystack));
        $this->assertFalse(Arr::in_array_recursive('garply', $haystack));

        // Test searching for a value with strict comparison
        $haystack = [1, 2, '3'];
        $this->assertTrue(Arr::in_array_recursive(3, $haystack));
        $this->assertTrue(Arr::in_array_recursive('3', $haystack));
        $this->assertFalse(Arr::in_array_recursive(3, $haystack, true));
        $this->assertTrue(Arr::in_array_recursive('3', $haystack, true));
    }

    /**
     * Tests Arr::test_reindex()
     *
     * @covers Velocite\Arr
     */
    public function test_reindex()
    {
        // Test reindexing a flat array
        $input = [1, 2, 3];
        $expected = [0 => 1, 1 => 2, 2 => 3];
        $this->assertSame($expected, Arr::reindex($input));

        // Test reindexing an associative array
        $input = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $expected = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $this->assertSame($expected, Arr::reindex($input));

        // Test reindexing a nested array
        $input = [
            'foo' => [1, 2, 3],
            'bar' => ['a' => 4, 'b' => 5, 'c' => 6],
            'baz' => 7,
        ];
        $expected = [
            'foo' => [0 => 1, 1 => 2, 2 => 3],
            'bar' => ['a' => 4, 'b' => 5, 'c' => 6],
            'baz' => 7,
        ];
        $this->assertSame($expected, Arr::reindex($input));
    }
}
