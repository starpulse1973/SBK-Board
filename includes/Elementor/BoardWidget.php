<?php
namespace SBKBoard\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use SBKBoard\DB\BoardRepository;

class BoardWidget extends Widget_Base {
	public function get_name(): string {
		return 'sbkboard_board';
	}

	public function get_title(): string {
		return esc_html__( "SBK Board - \u{AC8C}\u{C2DC}\u{D310}", 'sbk-board' );
	}

	public function get_icon(): string {
		return 'eicon-posts-ticker';
	}

	public function get_categories(): array {
		return [ 'sbkboard', 'general' ];
	}

	public function get_keywords(): array {
		return [ 'board', 'bulletin', 'forum', 'sbk' ];
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
				'default' => '0',
				'options' => $this->get_board_options(),
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
				'default' => 15,
			]
		);

		$this->add_control(
			'gallery_columns',
			[
				'label'   => esc_html__( "\u{D55C} \u{C904} \u{B2F9} \u{D45C}\u{C2DC} \u{AC1C}\u{C218}(\u{AC24}\u{B7EC}\u{B9AC} \u{C2A4}\u{D0A8}\u{C778} \u{ACBD}\u{C6B0})", 'sbk-board' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'step'    => 1,
				'default' => 3,
			]
		);

		$this->add_control(
			'board_title_max_length',
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
			'board_date_format',
			[
				'label'       => esc_html__( "\u{B0A0}\u{C9DC} \u{D45C}\u{C2DC} \u{D615}\u{C2DD}", 'sbk-board' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Y.m.d',
				'placeholder' => 'Y.m.d',
			]
		);

		$this->add_control(
			'board_search_width',
			[
				'label'       => esc_html__( "\u{AC80}\u{C0C9}\u{CC3D} \u{B108}\u{BE44}", 'sbk-board' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '260px',
				'placeholder' => '260px',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_basic',
			[
				'label' => esc_html__( "\u{AE30}\u{BCF8}", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'board_full_style_heading',
			[
				'label' => esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{C804}\u{CCB4} \u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'board_full_border_width',
			[
				'label'      => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{B450}\u{AED8}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 20 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-list' => 'border-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'board_full_border_style',
			[
				'label'   => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C885}\u{B958}", 'sbk-board' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'none'   => esc_html__( "\u{C5C6}\u{C74C}", 'sbk-board' ),
					'solid'  => esc_html__( "\u{C2E4}\u{C120}", 'sbk-board' ),
					'dashed' => esc_html__( "\u{D30C}\u{C120}", 'sbk-board' ),
					'dotted' => esc_html__( "\u{C810}\u{C120}", 'sbk-board' ),
				],
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'board_full_border_color',
			[
				'label'     => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'board_full_radius',
			[
				'label'      => esc_html__( "\u{BAA8}\u{C11C}\u{B9AC}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 40 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .sbkboard-list' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_control(
			'header_style_heading',
			[
				'label'     => esc_html__( "\u{AC8C}\u{C2DC}\u{D310} \u{D5E4}\u{B354} \u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'header_typography',
				'label'          => esc_html__( "\u{D14C}\u{C774}\u{BE14} \u{D5E4}\u{B354} \u{D0C0}\u{C774}\u{D3EC}\u{ADF8}\u{B798}\u{D53C}", 'sbk-board' ),
				'selector'       => '{{WRAPPER}} .sbkboard-list thead th',
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

		$this->add_control(
			'header_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list thead th' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'header_text_shadow',
				'selector' => '{{WRAPPER}} .sbkboard-list thead th',
			]
		);

		$this->add_control(
			'header_text_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list thead th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label'     => esc_html__( "\u{D14C}\u{C774}\u{BE14} \u{D5E4}\u{B354} \u{BC30}\u{ACBD}\u{C0C9}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list thead th' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'row_style_heading',
			[
				'label'     => esc_html__( "\u{D589}(Raw) \u{D14C}\u{B450}\u{B9AC} \u{C2A4}\u{D0C0}\u{C77C}", 'sbk-board' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
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
					'{{WRAPPER}} .sbkboard-list thead th' => 'border-bottom-width: {{SIZE}}px;',
					'{{WRAPPER}} .sbkboard-list tbody tr:not(:last-child) td' => 'border-bottom-width: {{SIZE}}px;',
					'{{WRAPPER}} .sbkboard-list tbody tr:last-child td' => 'border-bottom-width: 0;',
					'{{WRAPPER}} .sbkboard-post-header' => 'border-top-width: {{SIZE}}px; border-bottom-width: {{SIZE}}px;',
					'{{WRAPPER}} .sbkboard-post-content' => 'border-width: {{SIZE}}px;',
					'{{WRAPPER}} .sbkboard-comments' => 'border-top-width: {{SIZE}}px;',
					'{{WRAPPER}} .sbkboard-gallery-item' => 'border-width: {{SIZE}}px;',
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
					'{{WRAPPER}} .sbkboard-list thead th' => 'border-bottom-style: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-list tbody tr:not(:last-child) td' => 'border-bottom-style: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-list tbody tr:last-child td' => 'border-bottom-style: none;',
					'{{WRAPPER}} .sbkboard-post-header' => 'border-top-style: {{VALUE}}; border-bottom-style: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-post-content' => 'border-style: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-comments' => 'border-top-style: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-gallery-item' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'     => esc_html__( "\u{D14C}\u{B450}\u{B9AC} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list thead th' => 'border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-list tbody tr:not(:last-child) td' => 'border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-post-header' => 'border-top-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-post-content' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-comments' => 'border-top-color: {{VALUE}};',
					'{{WRAPPER}} .sbkboard-gallery-item' => 'border-color: {{VALUE}};',
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
					'{{WRAPPER}} .sbkboard-list' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
					'{{WRAPPER}} .sbkboard-gallery-item' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .sbkboard-post-content' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'row_hover_bg',
			[
				'label'     => esc_html__( "\u{D14C}\u{C774}\u{BE14} Hover \u{BC30}\u{ACBD}\u{C0C9}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list tbody tr:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8}", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_text_style_group_controls(
			'number',
			esc_html__( "\u{BC88}\u{D638}", 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-list .col-num'
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
				'selector'       => '{{WRAPPER}} .sbkboard-list .col-title a, {{WRAPPER}} .sbkboard-post-title, {{WRAPPER}} .sbkboard-gallery-info .title',
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
					'{{WRAPPER}} .sbkboard-list .col-title a, {{WRAPPER}} .sbkboard-post-title, {{WRAPPER}} .sbkboard-gallery-info .title' => '-webkit-text-stroke-width: {{SIZE}}px; text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'title_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list .col-title a, {{WRAPPER}} .sbkboard-post-title, {{WRAPPER}} .sbkboard-gallery-info .title' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .sbkboard-list .col-title a, {{WRAPPER}} .sbkboard-post-title, {{WRAPPER}} .sbkboard-gallery-info .title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list .col-title a, {{WRAPPER}} .sbkboard-post-title, {{WRAPPER}} .sbkboard-gallery-info .title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_style_group_controls(
			'author',
			esc_html__( "\u{C791}\u{C131}\u{C790}", 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-list .col-author'
		);

		$this->add_text_style_group_controls(
			'views',
			esc_html__( "\u{C870}\u{D68C}", 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-list .col-views'
		);

		$this->add_text_style_group_controls(
			'votes',
			esc_html__( "\u{CD94}\u{CC9C}", 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-list .col-votes'
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
				'selector'       => '{{WRAPPER}} .sbkboard-list .col-date, {{WRAPPER}} .sbkboard-post-meta, {{WRAPPER}} .sbkboard-gallery-info .meta',
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
					'{{WRAPPER}} .sbkboard-list .col-date, {{WRAPPER}} .sbkboard-post-meta, {{WRAPPER}} .sbkboard-gallery-info .meta' => '-webkit-text-stroke-width: {{SIZE}}px; text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'date_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list .col-date, {{WRAPPER}} .sbkboard-post-meta, {{WRAPPER}} .sbkboard-gallery-info .meta' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'date_text_shadow',
				'selector' => '{{WRAPPER}} .sbkboard-list .col-date, {{WRAPPER}} .sbkboard-post-meta, {{WRAPPER}} .sbkboard-gallery-info .meta',
			]
		);

		$this->add_control(
			'date_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .sbkboard-list .col-date, {{WRAPPER}} .sbkboard-post-meta, {{WRAPPER}} .sbkboard-gallery-info .meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_buttons_1',
			[
				'label' => esc_html__( "\u{BC84}\u{D2BC} 1", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'button_style_heading',
			[
				'label'     => esc_html__( '버튼 스타일', 'sbk-board' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_button_style_controls(
			'btn_write',
			esc_html__( '글쓰기 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-write'
		);
		$this->add_button_style_controls(
			'btn_submit',
			esc_html__( '등록 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-submit'
		);
		$this->add_button_style_controls(
			'btn_cancel',
			esc_html__( '취소 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-cancel'
		);
		$this->add_button_style_controls(
			'btn_search',
			esc_html__( '검색 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-search'
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_buttons_2',
			[
				'label' => esc_html__( "\u{BC84}\u{D2BC} 2", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_button_style_controls(
			'btn_list',
			esc_html__( '목록 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-list'
		);
		$this->add_button_style_controls(
			'btn_edit',
			esc_html__( '수정 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-edit'
		);
		$this->add_button_style_controls(
			'btn_delete',
			esc_html__( '삭제 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-delete'
		);
		$this->add_button_style_controls(
			'btn_comment_submit',
			esc_html__( '댓글 등록 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-btn-comment-submit'
		);
		$this->add_button_style_controls(
			'btn_vote_up',
			esc_html__( "\u{CD94}\u{CC9C} \u{BC84}\u{D2BC}", 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-vote-btn[data-type="up"]'
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_pagination',
			[
				'label' => esc_html__( "\u{D398}\u{C774}\u{C9C0}\u{B124}\u{C774}\u{C158}", 'sbk-board' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_button_style_controls(
			'btn_pagination_current',
			esc_html__( '선택된 페이지네이션 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-pagination .current'
		);
		$this->add_button_style_controls(
			'btn_pagination_default',
			esc_html__( '선택되지 않은 페이지네이션 버튼', 'sbk-board' ),
			'{{WRAPPER}} .sbkboard-pagination a'
		);

		$this->end_controls_section();
	}

	private function add_text_style_group_controls( string $prefix, string $label, string $selector ): void {
		$this->add_control(
			$prefix . '_style_heading',
			[
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $prefix . '_typography',
				'label'    => esc_html__( "\u{D0C0}\u{C774}\u{D3EC}\u{ADF8}\u{B798}\u{D53C}", 'sbk-board' ),
				'selector' => $selector,
			]
		);

		$this->add_responsive_control(
			$prefix . '_stroke_width',
			[
				'label'      => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{B450}\u{AED8}", 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 10 ],
				],
				'selectors'  => [
					$selector => '-webkit-text-stroke-width: {{SIZE}}px; text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			$prefix . '_stroke_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C2A4}\u{D2B8}\u{B85C}\u{D06C} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => $prefix . '_text_shadow',
				'selector' => $selector,
			]
		);

		$this->add_control(
			$prefix . '_color',
			[
				'label'     => esc_html__( "\u{D14D}\u{C2A4}\u{D2B8} \u{C0C9}\u{C0C1}", 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => 'color: {{VALUE}};',
				],
			]
		);
	}

	private function add_button_style_controls( string $prefix, string $label, string $selector, bool $with_hover = true ): void {
		$this->add_control(
			$prefix . '_heading',
			[
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $prefix . '_typography',
				'label'    => esc_html__( '타이포그래피', 'sbk-board' ),
				'selector' => $selector,
			]
		);

		$this->start_controls_tabs( $prefix . '_tabs' );

		$this->start_controls_tab(
			$prefix . '_tab_normal',
			[
				'label' => esc_html__( '기본', 'sbk-board' ),
			]
		);

		$this->add_control(
			$prefix . '_text_color',
			[
				'label'     => esc_html__( '텍스트 색상', 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$prefix . '_bg_color',
			[
				'label'     => esc_html__( '기본 색상', 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			$prefix . '_border_width',
			[
				'label'      => esc_html__( '테두리 두께', 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 20 ],
				],
				'selectors'  => [
					$selector => 'border-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			$prefix . '_border_style',
			[
				'label'     => esc_html__( '테두리 종류', 'sbk-board' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					'none'   => esc_html__( '없음', 'sbk-board' ),
					'solid'  => esc_html__( '실선', 'sbk-board' ),
					'dashed' => esc_html__( '파선', 'sbk-board' ),
					'dotted' => esc_html__( '점선', 'sbk-board' ),
					'double' => esc_html__( '이중선', 'sbk-board' ),
				],
				'selectors' => [
					$selector => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$prefix . '_border_color',
			[
				'label'     => esc_html__( '테두리 색상', 'sbk-board' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$selector => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			$prefix . '_radius',
			[
				'label'      => esc_html__( '모서리', 'sbk-board' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 40 ],
				],
				'selectors'  => [
					$selector => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			$prefix . '_padding',
			[
				'label'      => esc_html__( '패딩', 'sbk-board' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		if ( $with_hover ) {
			$hover_selector = $selector . ':hover, ' . $selector . ':focus';
			$this->start_controls_tab(
				$prefix . '_tab_hover',
				[
					'label' => esc_html__( '호버', 'sbk-board' ),
				]
			);

			$this->add_control(
				$prefix . '_hover_text_color',
				[
					'label'     => esc_html__( '텍스트 색상', 'sbk-board' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$hover_selector => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				$prefix . '_hover_bg_color',
				[
					'label'     => esc_html__( '마우스 호버시 색상', 'sbk-board' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$hover_selector => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				$prefix . '_hover_border_color',
				[
					'label'     => esc_html__( '테두리 색상', 'sbk-board' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$hover_selector => 'border-color: {{VALUE}};',
					],
				]
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$board_id = (int) ( $settings['board_id'] ?? 0 );
		if ( ! $board_id ) {
			echo '<p>' . esc_html__( "\u{AC8C}\u{C2DC}\u{D310}\u{C744} \u{C120}\u{D0DD}\u{D574} \u{C8FC}\u{C138}\u{C694}.", 'sbk-board' ) . '</p>';
			return;
		}

		$rpp              = max( 1, min( 50, (int) ( $settings['rpp'] ?? 15 ) ) );
		$gallery_columns  = max( 1, min( 6, (int) ( $settings['gallery_columns'] ?? 3 ) ) );
		$title_max_length = max( 10, min( 200, (int) ( $settings['board_title_max_length'] ?? 80 ) ) );
		$date_format      = sanitize_text_field( (string) ( $settings['board_date_format'] ?? '' ) );
		$search_width     = sanitize_text_field( (string) ( $settings['board_search_width'] ?? '' ) );
		$search_width     = preg_replace( '/\s+/', '', $search_width );
		if ( ! is_string( $search_width ) ) {
			$search_width = '';
		}
		if ( preg_match( '/^\d+(?:\.\d+)?$/', $search_width ) ) {
			$search_width .= 'px';
		}

		$shortcode = '[sbk_board id="' . esc_attr( (string) $board_id ) . '" rpp="' . esc_attr( (string) $rpp ) . '"';
		$shortcode .= ' gallery_columns="' . esc_attr( (string) $gallery_columns ) . '"';
		$shortcode .= ' title_max_length="' . esc_attr( (string) $title_max_length ) . '"';
		if ( '' !== $date_format ) {
			$shortcode .= ' date_format="' . esc_attr( $date_format ) . '"';
		}
		if ( '' !== $search_width ) {
			$shortcode .= ' search_width="' . esc_attr( $search_width ) . '"';
		}
		$shortcode .= ']';

		echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
