<?php
namespace SBKBoard\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use SBKBoard\DB\BoardRepository;

class LatestWidget extends Widget_Base {
	public function get_name(): string {
		return 'sbkboard_latest';
	}

	public function get_title(): string {
		return esc_html__( "SBK Board - \u{CD5C}\u{C2E0}\u{AE00}", 'sbk-board' );
	}

	public function get_icon(): string {
		return 'eicon-post-list';
	}

	public function get_categories(): array {
		return [ 'sbkboard', 'general' ];
	}

	public function get_keywords(): array {
		return [ 'sbk', 'latest', 'recent', 'board' ];
	}

	private function get_board_options(): array {
		$boards  = BoardRepository::get_all();
		$options = [ '0' => __( "-- \u{AC8C}\u{C2DC}\u{D310} \u{C120}\u{D0DD} --", 'sbk-board' ) ];
		foreach ( $boards as $board ) {
			$options[ (string) $board->id ] = esc_html( (string) $board->name );
		}
		return $options;
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( "\u{CF58}\u{D150}\u{CE20}", 'sbk-board' ),
			]
		);

		$this->add_control(
			'board_id',
			[
				'label'   => esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C120}\u{D0DD}", 'sbk-board' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_board_options(),
				'default' => '0',
			]
		);

		$this->add_control(
			'rpp',
			[
				'label'   => esc_html__( "\u{D45C}\u{C2DC} \u{AC1C}\u{C218}", 'sbk-board' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 50,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => esc_html__( "\u{D55C} \u{C904} \u{B2F9} \u{D45C}\u{C2DC} \u{AC1C}\u{C218}", 'sbk-board' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'step'    => 1,
				'default' => 3,
			]
		);

		$this->add_control(
			'page_url',
			[
				'label'       => esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{D398}\u{C774}\u{C9C0} \u{C8FC}\u{C18C}(\u{C120}\u{D0DD})", 'sbk-board' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'https://example.com/board/',
				'default'     => '',
			]
		);

		$this->add_control(
			'item_min_width',
			[
				'label'       => esc_html__( "\u{C544}\u{C774}\u{D15C} \u{AC00}\u{B85C} \u{D06C}\u{AE30}", 'sbk-board' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 80,
				'max'         => 600,
				'step'        => 1,
				'default'     => 0,
				'description' => esc_html__( "\u{C785}\u{B825}\u{D55C} \u{AC12}\u{C740} --sbk-latest-item-min(px)\u{B85C} \u{C801}\u{C6A9}\u{B429}\u{B2C8}\u{B2E4}. 0\u{C740} \u{C790}\u{B3D9} \u{ACC4}\u{C0B0}\u{C785}\u{B2C8}\u{B2E4}.", 'sbk-board' ),
			]
		);

		$this->add_control(
			'title_max_length',
			[
				'label'   => esc_html__( "\u{C81C}\u{BAA9} \u{CD5C}\u{B300} \u{AE38}\u{C774}", 'sbk-board' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 10,
				'max'     => 200,
				'step'    => 1,
				'default' => 80,
			]
		);

		$this->add_control(
			'date_format',
			[
				'label'       => esc_html__( "\u{B0A0}\u{C9DC} \u{D45C}\u{C2DC} \u{D615}\u{C2DD}", 'sbk-board' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Y.m.d',
				'placeholder' => 'Y.m.d',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( "\u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'row_style_heading',
			[
				'label' => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'border_width',
			[
				'label'      => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{B450}\u{AED8}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 20 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-latest' => 'border-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'border_style',
			[
				'label'     => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C885}\u{B958}", 'sbk-board' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					'none'   => esc_html__( "\u{C5C6}\u{C74C}", 'sbk-board' ),
					'solid'  => esc_html__( "\u{C2E4}\u{C120}", 'sbk-board' ),
					'dashed' => esc_html__( "\u{D30C}\u{C120}", 'sbk-board' ),
					'dotted' => esc_html__( "\u{C810}\u{C120}", 'sbk-board' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'     => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'radius',
			[
				'label'      => esc_html__( "\u{BAA8}\u{C11C}\u{B9AC}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 40 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-latest' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_style_heading',
			[
				'label'     => esc_html__( "\u{AC8C}\u{C2DC}\u{BB3C} \u{C81C}\u{BAA9}", 'sbk-board' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'title_typography',
				'label'          => esc_html__( "\u{D0C0}\u{C774}\u{D3EC}\u{ADF8}\u{B798}\u{D53C}", 'sbk-board' ),
				'selector'       => '{{WRAPPER}} .sbkboard-latest-link',
				'fields_options' => [
					'font_size'      => [
						'label'      => esc_html__( "\u{AE00}\u{C790} \u{D06C}\u{AE30}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
					'line_height'    => [
						'label'      => esc_html__( "\u{C904} \u{B192}\u{C774}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
					'letter_spacing' => [
						'label'      => esc_html__( "\u{C790}\u{AC04}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
				],
			]
		);

		$this->add_responsive_control(
			'title_stroke_width',
			[
				'label'      => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{B450}\u{AED8}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 10 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-latest-link' => '-webkit-text-stroke-width: {{SIZE}}px; text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'title_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest-link' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .sbkboard-latest-link',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'date_style_heading',
			[
				'label'     => esc_html__( "\u{AC8C}\u{C2DC}\u{BB3C} \u{B0A0}\u{C9DC}", 'sbk-board' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'date_typography',
				'label'          => esc_html__( "\u{D0C0}\u{C774}\u{D3EC}\u{ADF8}\u{B798}\u{D53C}", 'sbk-board' ),
				'selector'       => '{{WRAPPER}} .sbkboard-latest-meta',
				'fields_options' => [
					'font_size'      => [
						'label'      => esc_html__( "\u{AE00}\u{C790} \u{D06C}\u{AE30}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
					'line_height'    => [
						'label'      => esc_html__( "\u{C904} \u{B192}\u{C774}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
					'letter_spacing' => [
						'label'      => esc_html__( "\u{C790}\u{AC04}", 'sbk-board' ),
						'responsive' => true,
						'size_units' => [ 'px', 'em', 'rem' ],
					],
				],
			]
		);

		$this->add_responsive_control(
			'date_stroke_width',
			[
				'label'      => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{B450}\u{AED8}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 10 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-latest-meta' => '-webkit-text-stroke-width: {{SIZE}}px; text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'date_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest-meta' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'date_text_shadow',
				'selector' => '{{WRAPPER}} .sbkboard-latest-meta',
			]
		);

		$this->add_control(
			'date_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-latest-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$board_id = (int) ( $settings['board_id'] ?? 0 );
		if ( $board_id <= 0 ) {
			echo '<p>' . esc_html__( "\u{AC8C}\u{C2DC}\u{D310}\u{C744} \u{C120}\u{D0DD}\u{D574} \u{C8FC}\u{C138}\u{C694}.", 'sbk-board' ) . '</p>';
			return;
		}

		$rpp      = max( 1, min( 50, (int) ( $settings['rpp'] ?? 5 ) ) );
		$columns  = max( 1, min( 6, (int) ( $settings['columns'] ?? min( 6, $rpp ) ) ) );
		$item_min = max( 0, (int) ( $settings['item_min_width'] ?? 0 ) );
		$title_max_length = max( 10, min( 200, (int) ( $settings['title_max_length'] ?? 80 ) ) );
		$date_format      = sanitize_text_field( (string) ( $settings['date_format'] ?? '' ) );
		$page_url = esc_url_raw( (string) ( $settings['page_url'] ?? '' ) );

		$shortcode = '[sbk_latest board_id="' . esc_attr( (string) $board_id ) . '" rpp="' . esc_attr( (string) $rpp ) . '"';
		$shortcode .= ' columns="' . esc_attr( (string) $columns ) . '"';
		if ( $item_min > 0 ) {
			$shortcode .= ' item_min="' . esc_attr( (string) $item_min ) . '"';
		}
		$shortcode .= ' title_max_length="' . esc_attr( (string) $title_max_length ) . '"';
		if ( '' !== $date_format ) {
			$shortcode .= ' date_format="' . esc_attr( $date_format ) . '"';
		}
		if ( '' !== $page_url ) {
			$shortcode .= ' page_url="' . esc_attr( $page_url ) . '"';
		}
		$shortcode .= ']';

		echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
