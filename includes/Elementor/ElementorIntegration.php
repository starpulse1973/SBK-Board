<?php
namespace SBKBoard\Elementor;

class ElementorIntegration {
	private static bool $registered = false;

	private static function ensure_widget_class( string $class_name, string $file_name ): bool {
		if ( class_exists( $class_name ) ) {
			return true;
		}

		$path = SBKBOARD_DIR . 'includes/Elementor/' . $file_name;
		if ( is_readable( $path ) ) {
			require_once $path;
		}

		return class_exists( $class_name );
	}

	public static function init(): void {
		add_action( 'elementor/elements/categories_registered', [ self::class, 'register_category' ] );
		add_action( 'elementor/widgets/register', [ self::class, 'register_widgets' ] );
		add_action( 'elementor/widgets/widgets_registered', [ self::class, 'register_widgets_legacy' ] );
		add_action( 'elementor/element/sbkboard_latest/section_advanced/before_section_end', [ self::class, 'tune_latest_advanced_controls' ], 10, 2 );
	}

	public static function tune_latest_advanced_controls( $element, array $args ): void {
		if ( ! $element || ! method_exists( $element, 'update_responsive_control' ) ) {
			return;
		}

		$element->update_responsive_control(
			'_margin',
			[
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$element->update_responsive_control(
			'_padding',
			[
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	public static function register_category( $elements_manager ): void {
		if ( method_exists( $elements_manager, 'add_category' ) ) {
			$elements_manager->add_category(
				'sbkboard',
				[
					'title' => __( 'SBK Board', 'sbk-board' ),
					'icon'  => 'fa fa-comments',
				]
			);
		}
	}

	public static function register_widgets( $widgets_manager ): void {
		if ( self::$registered ) {
			return;
		}

		$register = method_exists( $widgets_manager, 'register' )
			? 'register'
			: ( method_exists( $widgets_manager, 'register_widget_type' ) ? 'register_widget_type' : null );

		if ( ! $register ) {
			return;
		}

		$widget_classes = [];

		if ( self::ensure_widget_class( BoardWidget::class, 'BoardWidget.php' ) ) {
			$widget_classes[] = BoardWidget::class;
		}
		if ( self::ensure_widget_class( LatestWidget::class, 'LatestWidget.php' ) ) {
			$widget_classes[] = LatestWidget::class;
		}

		if ( empty( $widget_classes ) ) {
			return;
		}

		foreach ( $widget_classes as $widget_class ) {
			try {
				$widgets_manager->{$register}( new $widget_class() );
			} catch ( \Throwable $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[SBK Board] Elementor widget registration failed: ' . $widget_class . ' - ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}

		self::$registered = true;
	}

	public static function register_widgets_legacy(): void {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! \Elementor\Plugin::instance() ) {
			return;
		}
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager ?? null;
		if ( ! $widgets_manager ) {
			return;
		}
		self::register_widgets( $widgets_manager );
	}
}
