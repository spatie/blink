<?php

namespace Spatie\Blink\Test;

use Spatie\Blink\Blink;
use PHPUnit\Framework\TestCase;

class BlinkTest extends TestCase
{
    /** @var string */
    protected $storageFile;

    /** @var \Spatie\Blink\Blink */
    protected $blink;

    public function setUp()
    {
        parent::setUp();

        $this->blink = new Blink();
    }

    /** @test */
    public function it_can_store_a_key_value_pair()
    {
        $this->blink->put('key', 'value');

        $this->assertSame('value', $this->blink->get('key'));
    }

    /** @test */
    public function it_can_store_an_array()
    {
        $testArray = ['one' => 1, 'two' => 2];

        $this->blink->put('key', $testArray);

        $this->assertSame($testArray, $this->blink->get('key'));
    }

    /** @test */
    public function it_can_determine_if_the_blink_cache_holds_a_value_for_a_given_name()
    {
        $this->assertFalse($this->blink->has('key'));

        $this->blink->put('key', 'value');

        $this->assertTrue($this->blink->has('key'));
    }

    /** @test */
    public function it_can_determine_if_the_blink_cache_holds_a_value_for_a_given_name_with_a_wild_card()
    {
        $this->assertFalse($this->blink->has('prefix.*.suffix'));

        $this->blink->put('prefix.middle.suffix', 'value');

        $this->assertTrue($this->blink->has('prefix.*.suffix'));
        $this->assertTrue($this->blink->has('*.suffix'));
        $this->assertTrue($this->blink->has('prefix.*'));
        $this->assertTrue($this->blink->has('*'));
        $this->assertFalse($this->blink->has('*.no'));
        $this->assertFalse($this->blink->has('no.*'));
    }

    /** @test */
    public function it_will_return_the_default_value_when_using_a_non_existing_key()
    {
        $this->assertSame('default', $this->blink->get('key', 'default'));
    }

    /** @test */
    public function it_will_return_an_array_when_getting_values_using_a_wildcard()
    {
        $this->blink->put('prefix.1.suffix', 'value1');
        $this->blink->put('prefix.2.suffix', 'value2');
        $this->blink->put('prefix.1', 'value3');
        $this->blink->put('1.suffix', 'value4');

        $this->assertSame([
            'prefix.1.suffix' => 'value1',
            'prefix.2.suffix' => 'value2',
        ], $this->blink->get('prefix.*.suffix'));
    }

    /** @test */
    public function it_will_return_the_default_value_when_getting_values_using_a_wildcard_that_has_no_matches()
    {
        $this->blink->put('prefix.1.suffix', 'value1');
        $this->blink->put('prefix.2.suffix', 'value2');
        $this->blink->put('prefix.1', 'value3');
        $this->blink->put('1.suffix', 'value4');

        $this->assertSame('default', $this->blink->get('non.*.existant', 'default'));
    }

    /** @test */
    public function it_can_store_an_integer()
    {
        $this->blink->put('number', 1);

        $this->assertSame(1, $this->blink->get('number'));
    }

    /** @test */
    public function it_provides_a_chainable_put_method()
    {
        $this->blink
            ->put('number', 1)
            ->put('string', 'hello');

        $this->assertSame(1, $this->blink->get('number'));
        $this->assertSame('hello', $this->blink->get('string'));
    }

    /** @test */
    public function it_will_return_null_for_a_non_existing_value()
    {
        $this->assertNull($this->blink->get('non existing key'));
    }

    /** @test */
    public function it_can_overwrite_a_value()
    {
        $this->blink->put('key', 'value');

        $this->blink->put('key', 'otherValue');

        $this->assertSame('otherValue', $this->blink->get('key'));
    }

    /** @test */
    public function it_can_fetch_all_values_at_once()
    {
        $this->blink->put('key', 'value');

        $this->blink->put('otherKey', 'otherValue');

        $this->assertSame([
            'key' => 'value',
            'otherKey' => 'otherValue',
        ], $this->blink->all());
    }

    /** @test */
    public function it_can_store_multiple_value_pairs_in_one_go()
    {
        $values = [
            'key' => 'value',
            'otherKey' => 'otherValue',
        ];

        $this->blink->put($values);

        $this->assertSame('value', $this->blink->get('key'));

        $this->assertSame($values, $this->blink->all());
    }

    /** @test */
    public function it_can_store_values_without_forgetting_the_old_values()
    {
        $this->blink->put('test1', 'value1');
        $this->blink->put('test2', 'value2');

        $this->assertSame([
            'test1' => 'value1',
            'test2' => 'value2',
        ], $this->blink->all());

        $this->blink->put(['test3' => 'value3']);

        $this->assertSame([
            'test1' => 'value1',
            'test2' => 'value2',
            'test3' => 'value3',
        ], $this->blink->all());
    }

    /** @test */
    public function it_can_forget_a_value()
    {
        $this->blink->put('key', 'value');
        $this->blink->put('otherKey', 'otherValue');
        $this->blink->put('otherKey2', 'otherValue2');

        $this->blink->forget('otherKey');

        $this->assertSame('value', $this->blink->get('key'));

        $this->assertNull($this->blink->get('otherKey'));

        $this->assertSame('otherValue2', $this->blink->get('otherKey2'));
    }

    /** @test */
    public function it_can_forget_multiple_values_at_once_using_a_wildcard()
    {
        $this->blink->put('prefix.1.suffix', 'value1');
        $this->blink->put('prefix.2.suffix', 'value2');
        $this->blink->put('prefix.1', 'value3');
        $this->blink->put('1.suffix', 'value4');

        $this->blink->forget('prefix.*.suffix');

        $this->assertSame([
            'prefix.1' => 'value3',
            '1.suffix' => 'value4',
        ], $this->blink->all('key'));
    }

    /** @test */
    public function it_can_flush_the_entire_value_store()
    {
        $this->blink->put('key', 'value');

        $this->blink->put('otherKey', 'otherValue');

        $this->blink->flush();

        $this->assertNull($this->blink->get('key'));

        $this->assertNull($this->blink->get('otherKey'));
    }

    /** @test */
    public function it_will_return_an_empty_array_when_getting_all_content()
    {
        $this->assertSame([], $this->blink->all());
    }

    /** @test */
    public function it_can_get_and_forget_a_value()
    {
        $this->blink->put('key', 'value');

        $this->assertSame('value', $this->blink->pull('key'));

        $this->assertNull($this->blink->get('key'));
    }

    /** @test */
    public function it_can_get_and_forget_a_values_using_a_wildcard()
    {
        $this->blink->put('prefix.1.suffix', 'value1');
        $this->blink->put('prefix.2.suffix', 'value2');
        $this->blink->put('prefix.1', 'value3');
        $this->blink->put('1.suffix', 'value4');

        $this->assertSame([
            'prefix.1.suffix' => 'value1',
            'prefix.2.suffix' => 'value2',
        ], $this->blink->pull('prefix.*.suffix'));

        $this->assertSame([
            'prefix.1' => 'value3',
            '1.suffix' => 'value4',
        ], $this->blink->all());
    }

    /** @test */
    public function it_can_increment_a_new_value()
    {
        $returnValue = $this->blink->increment('number');

        $this->assertSame(1, $returnValue);

        $this->assertSame(1, $this->blink->get('number'));
    }

    /** @test */
    public function it_can_increment_an_existing_value()
    {
        $this->blink->put('number', 1);

        $returnValue = $this->blink->increment('number');

        $this->assertSame(2, $returnValue);

        $this->assertSame(2, $this->blink->get('number'));
    }

    /** @test */
    public function it_can_increment_a_value_by_another_value()
    {
        $returnValue = $this->blink->increment('number', 2);

        $this->assertSame(2, $returnValue);

        $this->assertSame(2, $this->blink->get('number'));

        $returnValue = $this->blink->increment('number', 2);

        $this->assertSame(4, $returnValue);

        $this->assertSame(4, $this->blink->get('number'));
    }

    /** @test */
    public function it_can_decrement_a_new_value()
    {
        $returnValue = $this->blink->decrement('number');

        $this->assertSame(-1, $returnValue);

        $this->assertSame(-1, $this->blink->get('number'));
    }

    /** @test */
    public function it_can_decrement_an_existing_value()
    {
        $this->blink->put('number', 10);

        $returnValue = $this->blink->decrement('number');

        $this->assertSame(9, $returnValue);

        $this->assertSame(9, $this->blink->get('number'));
    }

    /** @test */
    public function it_can_decrement_a_value_by_another_value()
    {
        $returnValue = $this->blink->decrement('number', 2);

        $this->assertSame(-2, $returnValue);

        $this->assertSame(-2, $this->blink->get('number'));

        $returnValue = $this->blink->decrement('number', 2);

        $this->assertSame(-4, $returnValue);

        $this->assertSame(-4, $this->blink->get('number'));
    }

    /** @test */
    public function it_implements_array_access()
    {
        $this->assertFalse(isset($this->blink['key']));

        $this->blink['key'] = 'value';

        $this->assertTrue(isset($this->blink['key']));

        $this->assertSame('value', $this->blink['key']);

        unset($this->blink['key']);

        $this->assertFalse(isset($this->blink['key']));

        $this->assertNull($this->blink['key']);
    }

    /** @test */
    public function it_implements_countable()
    {
        $this->assertCount(0, $this->blink);

        $this->blink->put('key', 'value');

        $this->assertCount(1, $this->blink);
    }

    /** @test */
    public function it_can_perform_a_function_only_once()
    {
        $callable = function() {
            return rand();
        };

        $firstResult = $this->blink->once('random', $callable);

        $this->assertNotNull($firstResult);

        foreach(range(1,10) as $index) {
            $this->assertSame($firstResult, $this->blink->once('random', $callable));
        }
    }
}
