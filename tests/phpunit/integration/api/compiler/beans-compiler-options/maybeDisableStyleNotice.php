<?php
/**
 * Tests the maybe_disable_style_notice() method of _Beans_Compiler_Options.
 *
 * @package Beans\Framework\Tests\Integration\API\Compiler
 *
 * @since   1.5.0
 */

namespace Beans\Framework\Tests\Integration\API\Compiler;

use _Beans_Compiler_Options;
use Beans\Framework\Tests\Integration\API\Compiler\Includes\Compiler_Options_Test_Case;

require_once dirname( __DIR__ ) . '/includes/class-compiler-options-test-case.php';

/**
 * Class Tests_BeansCompilerOptions_MaybeDisableStyleNotice
 *
 * @package Beans\Framework\Tests\Integration\API\Compiler
 * @group   api
 * @group   api-compiler
 */
class Tests_BeansCompilerOptions_MaybeDisableStyleNotice extends Compiler_Options_Test_Case {

	/**
	 * Test _Beans_Compiler_Options::maybe_disable_style_notice() should not render when compile styles not an option.
	 */
	public function test_should_not_render_when_compile_styles_not_an_option() {
		$this->assertEmpty( get_option( 'beans_compile_all_styles' ) );

		ob_start();
		( new _Beans_Compiler_Options() )->maybe_disable_style_notice();

		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Test _Beans_Compiler_Options::maybe_disable_style_notice() should not render when Compiler is not in dev mode.
	 */
	public function test_should_not_render_when_compiler_not_in_dev_mode() {
		add_option( 'beans_compile_all_styles', 1 );
		$this->assertEquals( 1, get_option( 'beans_compile_all_styles' ) );
		$this->assertFalse( _beans_is_compiler_dev_mode() );

		ob_start();
		( new _Beans_Compiler_Options() )->maybe_disable_style_notice();

		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Test _Beans_Compiler_Options::maybe_disable_style_notice() should render when compile styles is selected and Compiler
	 * is in dev mode.
	 */
	public function test_should_render_when_compile_styles_selected_and_compiler_in_dev_mode() {
		add_option( 'beans_compile_all_styles', 1 );
		add_option( 'beans_dev_mode', 1 );
		$this->assertEquals( 1, get_option( 'beans_compile_all_styles' ) );
		$this->assertTrue( _beans_is_compiler_dev_mode() );

		ob_start();
		( new _Beans_Compiler_Options() )->maybe_disable_style_notice();
		$actual = ob_get_clean();

		$expected = <<<EOB
<br />		
<span style="color: #d85030;">Styles are not compiled in development mode.</span>
EOB;
		$this->assertSame( $this->format_the_html( $expected ), $this->format_the_html( $actual ) );
	}
}
