<?php
namespace TDD\Test;
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .'autoload.php';

use PHPUnit\Framework\TestCase;
use TDD\Receipt;

class ReceiptTest extends TestCase {
	public function setUp() {
		$this->Receipt = new Receipt();
	}

	public function tearDown() {
		unset($this->Receipt);
	}

	/**
	 * @dataProvider provideTotal
	 */
	public function testTotal($items, $expected) {
	    // Kontrollib, et Receipt klassi total funktsioon tagastaks provideTotal poolt antavatele andmetele vastavad tulemused.
        // Kupongi väärtus selle testi puhul on alati null, seega kupongi toimivust ei arvestata.
		$coupon = null;
		$output = $this->Receipt->total($items, $coupon);
		$this->assertEquals(
			$expected,
			$output,
			"When summing the total should equal {$expected}"
		);
	}

	public function provideTotal() {
		return [
			'ints totaling 16' => [[1,2,5,8], 16],
			[[-1,2,5,8], 14],
			[[1,2,8], 11],
		];
	}
	public function testTotalAndCoupon() {
	    // Testib total funktsiooni toimivust, kasutades kupongi. Seekord on esemete väärtused antud funktsiooni sees $input muutujana.
        // Antud esemete summa on 15 ja kupong peaks summat 20% võrra vähendama, seega oodatav tulemus on 12.
		$input = [0,2,5,8];
		$coupon = 0.20;
		$output = $this->Receipt->total($input, $coupon);
		$this->assertEquals(
			12,
			$output,
			'When summing the total should equal 12'
		);
	}

	public function testTotalException() {
	    // Testib, et total funktsioon ei laseks kasutada kupongi, mis vähendab esemete summat rohkem kui 100% võrra.
		$input = [0,2,5,8];
		$coupon = 1.20;
		$this->expectException('BadMethodCallException');
		$this->Receipt->total($input, $coupon);
	}

	public function testPostTaxTotal() {
		$items = [1,2,5,8];
		$tax = 0.20;
		$coupon = null;
		// Testib total ja tax funktsioone mocki kasutades.
		$Receipt = $this->getMockBuilder('TDD\Receipt')
			->setMethods(['tax', 'total'])
			->getMock();
		$Receipt->expects($this->once())
			->method('total')
			->with($items, $coupon)
			->will($this->returnValue(10.00));
		$Receipt->expects($this->once())
			->method('tax')
			->with(10.00, $tax)
			->will($this->returnValue(1.00));
		// Testib postTaxTotal funktsiooni toimivust.
		$result = $Receipt->postTaxTotal([1,2,5,8], 0.20, null);
		$this->assertEquals(11.00, $result);
	}

	public function testTax() {
        // Testib, et tax funktsioon tagastaks inputAmounti ja taxInputi summa.
		$inputAmount = 10.00;
		$taxInput = 0.10;
		$output = $this->Receipt->tax($inputAmount, $taxInput);
		$this->assertEquals(
			1.00,
			$output,
			'The tax calculation should equal 1.00'
		);
	}
}