<?php
namespace Psalm\Tests;

use Psalm\Config;
use Psalm\Context;

class ThrowsAnnotationTest extends TestCase
{
    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage MissingThrowsDocblock
     *
     * @return                   void
     */
    public function testUndocumentedThrow()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return void
     */
    public function testDocumentedThrow()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return void
     */
    public function testDocumentedParentThrow()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws Exception
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return void
     */
    public function testNoThrowWhenSuppressing()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @psalm-suppress MissingThrowsDocblock
                 */
                function foo() : void {
                    if (rand(0, 1)) {
                        throw new \UnexpectedValueException();
                    }
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage MissingThrowsDocblock
     *
     * @return                   void
     */
    public function testUndocumentedThrowInFunctionCall()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                function bar(int $x, int $y) : void {
                    foo($x, $y);
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return                   void
     */
    public function testDocumentedThrowInFunctionCall()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function bar(int $x, int $y) : void {
                    foo($x, $y);
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return                   void
     */
    public function testDocumentedThrowInFunctionCallWithoutThrow()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                class Foo
                {
                    /**
                     * @throws \TypeError
                     */
                    public static function notReallyThrowing(int $a): string
                    {
                        if ($a > 0) {
                            return "";
                        }

                        return (string) $a;
                    }

                    public function test(): string
                    {
                        try {
                            return self::notReallyThrowing(2);
                        } catch (\Throwable $E) {
                            return "";
                        }
                    }
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return void
     */
    public function testCaughtThrowInFunctionCall()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                function bar(int $x, int $y) : void {
                    try {
                        foo($x, $y);
                    } catch (RangeException $e) {

                    } catch (InvalidArgumentException $e) {}
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage MissingThrowsDocblock
     *
     * @return                   void
     */
    public function testUncaughtThrowInFunctionCall()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                function bar(int $x, int $y) : void {
                    try {
                        foo($x, $y);
                    } catch (\RangeException $e) {

                    }
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage MissingDocblockType
     *
     * @return                   void
     */
    public function testEmptyThrows()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws
                 */
                function foo(int $x, int $y) : int {}'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return void
     */
    public function testCaughtAllThrowInFunctionCall()
    {
        Config::getInstance()->check_for_throws_docblock = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                function bar(int $x, int $y) : void {
                    try {
                        foo($x, $y);
                    } catch (Exception $e) {}
                }'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage UncaughtThrowInGlobalScope
     *
     * @return                   void
     */
    public function testUncaughtDocumentedThrowCallInGlobalScope()
    {
        Config::getInstance()->check_for_throws_in_global_scope = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                foo(0, 0);'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return                   void
     */
    public function testCaughtDocumentedThrowCallInGlobalScope()
    {
        Config::getInstance()->check_for_throws_docblock = true;
        Config::getInstance()->check_for_throws_in_global_scope = true;

        $this->addFile(
            'somefile.php',
            '<?php
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                try {
                    foo(0, 0);
                } catch (Exception $e) {}'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @return                   void
     */
    public function testUncaughtUndocumentedThrowCallInGlobalScope()
    {
        Config::getInstance()->check_for_throws_in_global_scope = true;

        $this->addFile(
            'somefile.php',
            '<?php
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                foo(0, 0);'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }

    /**
     * @expectedException        \Psalm\Exception\CodeException
     * @expectedExceptionMessage UncaughtThrowInGlobalScope
     *
     * @return                   void
     */
    public function testUncaughtDocumentedThrowCallInNamespace()
    {
        Config::getInstance()->check_for_throws_in_global_scope = true;

        $this->addFile(
            'somefile.php',
            '<?php
                namespace ns;
                /**
                 * @throws RangeException
                 * @throws InvalidArgumentException
                 */
                function foo(int $x, int $y) : int {
                    if ($y === 0) {
                        throw new \RangeException("Cannot divide by zero");
                    }

                    if ($y < 0) {
                        throw new \InvalidArgumentException("This is also bad");
                    }

                    return intdiv($x, $y);
                }

                foo(0, 0);'
        );

        $context = new Context();

        $this->analyzeFile('somefile.php', $context);
    }
}
