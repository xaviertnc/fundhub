/* global wp, wpApiSettings, jQuery, lodash */

/* 
 * TIPS:
 *
 *  - Wordpress adds various usefull global objects to the "window" scope when
 *    we're on the Wordpress editor page. All the toys... Check it out in your
 *    browser inspector!
 *
 *  - WP REST API - See window.wpApiSettings to get the API base URL.
 *    NB: To get the FULL API SCHEMA, enter the API base url in your browser!
 *
 *  - Use https://babeljs.io/repl and select "react" to transpile JSX to JS
 *
 *  - Make sure you know your JSX and React syntax, otherwise working on this
 *    component will drive you NUTS! Know about React store functions like
 *    select('store/name') and dispatch('store/name'). E.g. select('core/editor')
 *
 *  - Often check how the post and block is saved in MySQL Adminer! Make sure
 *    all the attributes you're using in your render code is saved in the
 *    block comment part of the DB string.
 *  
 *  - The save() function is used to render what will be saved as the "content"
 *    of your block in the DB. This does NOT have to match the HTML you use
 *    in Editor Mode! In fact, you can make this function return NULL.
 *
 *  - Set the save() function to return NULL. This will prevent the
 *    "this component is invalid" issue while developing your block UI.
 *    Return NULL is also used to make your block "dynamic".
 *
 *  - To make your block "dynamic", you need to set the "render_callback" param
 *    in register_block_type() in functions.php. Remember to also add the render
 *    function you specified in your functions.php to render the block's HTML.
 */

(function (wp) {

  /* NOTE: wp.element === React  i.e. wp.element.createElement === el */
  var el = wp.element.createElement;
  /* Inspect wp.components in the browser console to see what's available! */
  var components = wp.components;
  /* Block editor controls shown on-top of each block in the editor when selected */
  var blockControls = wp.blockEditor.BlockControls;
  /* Block property controls in the right-hand editor sidebar */
  var inspectorControls = wp.blockEditor.InspectorControls;
  /* Data API, fetch data promise */
  var withSelect = wp.data.withSelect;
  /* Translation */
  var __ = wp.i18n.__;


  /* Used when we register dynamic guten blocks */
  function returnNull( props ) { return null; };
  
  
  /* Shows loading spinner next to message */
  function pleaseWait( message )
  {
	return el('div', { className: 'please-wait' },
	  [
	    el('span', null, __( message )),
	    el('div', { className: 'lds-ring' },
	      [ el('div'), el('div'), el('div'), el('div') ]
	    )
	  ]
	);      
  }


  /* Single Asset Manager Strategies Tag Cloud */

  var asm_strategies_fetchData = withSelect(function( select, ownProps )
  {
	var core = wp.data.select('core');
	var editor = wp.data.select('core/editor');
    var post_id = editor.getCurrentPostId();
	var manager_strategies = core.getEntityRecords( 'taxonomy', 'strategy',
	    { post: post_id, per_page: 100 } );
    return { manager_strategies };
  });

  var asm_tagcloud_renderEditMode = function( props ) {
	if ( ! props.manager_strategies ) {
	  return pleaseWait( 'Initializing tag cloud.' );
	}
  	var inspectorElements = [];
  	var manager_strategies = props.manager_strategies.map(function (strategy) {
	  return el('li', null, el('a', { href: '#' + strategy.name }, strategy.name));
	});
    return [
      el(blockControls, {key: 'controls'}),
      el(inspectorControls, {key: 'inspector'},
        el(components.PanelBody, {initialOpen: true},
          inspectorElements
        )
      ),
	  el('ul', { class: 'post-strategies-block' },
	    manager_strategies.length ? manager_strategies
	      : el('li', null, 'Wait for strategies tag cloud to load.')
	  )
	];
  };

  wp.blocks.registerBlockType( 'fundhub/asm-strategies-tagcloud', {
    title: 'FundHub - Strategy Tags',
    icon: 'tagcloud',
    category: 'widgets',
    attributes: {},
    edit: asm_strategies_fetchData( asm_tagcloud_renderEditMode ),
    save: returnNull,
  } );


  /* Asset Manager Strategies Page - Auto Content Generator Block */

  var strategies_page_fetchData = withSelect(function( select, ownProps )
  {
	var core = wp.data.select('core');
	var all_strategies = core.getEntityRecords( 'taxonomy', 'strategy', 
	    { per_page: 100 } );
    return { all_strategies };
  });
  
  var strategies_page_renderEditMode = function( props )
  {
	if ( ! props.all_strategies ) {
	  return pleaseWait( 'Initializing strategies info.' );
	}
    /* Note: Saving data to the DB directly here is not ideal!  We would rather
     * only save when we hit the "UPDATE" button.  We should then also clear
     * our attributes so they don't take space in the post content.
     */
    function update( value )
    {
      this.strategy.description = value;
      var method = 'POST';
      var data = { description: value };
      var url = wpApiSettings.root + wpApiSettings.versionString + 'strategy/' + this.strategy.id;
      var beforeSend = function ( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce ); };
      jQuery.ajax({ method, url, beforeSend, data }).done(function(resp) { console.log( resp ); } );      
    }
  	var inspectorElements = [];
  	var all_strategies = props.all_strategies.map(function (strategy) {
	  return el('li', null, el('a', null, strategy.name));
	});
	var strategies_edit_zone = props.all_strategies.map(function (strategy) {
	  return el('dl', { className: 'strategy-block' }, [
	    el('dt', null, strategy.name),
	    props.isSelected
	      ? el( wp.blockEditor.RichText, {
			  type: 'text',
			  placeholder: 'Enter a description for *' + strategy.name + '* here...',
			  value: strategy.description,
			  onChange: lodash.throttle(update.bind({ strategy }), 5000),
		    } )
		  : el( wp.blockEditor.RichText.Content, {
		      tagName: 'dd',
		      value: strategy.description,
		    } ),
	  ] );
	});
    return [
      el(blockControls, {key: 'controls'}),
      el(inspectorControls, {key: 'inspector'},
        el(components.PanelBody, {initialOpen: true},
          inspectorElements
        )
      ),
      all_strategies.length ? el('ul', { class: 'strategies-block' }, 
        all_strategies) : el('p', null, 'Wait for the strategies content to load.'),
      strategies_edit_zone ? strategies_edit_zone : ''
	];
  };

  wp.blocks.registerBlockType( 'fundhub/strategies-page', {
    title: 'FundHub - Strategies',
    icon: 'chart-line',
    category: 'widgets',
    attributes: {},
    edit: strategies_page_fetchData( strategies_page_renderEditMode ),
    save: returnNull,
  } );


  /* Raw Code Block */

//   var code_renderEditMode = function( props )
//   {
//     function update( event )
//     {
//       props.setAttributes( { code: event.target.value } );
//     }
//     var inspectorElements = [];
//     return [
//       el(blockControls, {key: 'controls'}),
//       el(inspectorControls, {key: 'inspector'},
//         el(components.PanelBody, {initialOpen: true},
//           inspectorElements
//         )
//       ),
//       el('label', null, 'Enter inline code here:'), 
//       el('textarea', { class: 'fh-code', rows: 7, 
//         value: props.attributes.code, onChange: update }),
// 	];
//   };

//   wp.blocks.registerBlockType( 'fundhub/code', {
//     title: 'FundHub - Code',
//     icon: 'editor-code',              // media-code
//     category: 'widgets',
//     attributes: {
//       code: {type: 'string'},
//     },
//     edit: code_renderEditMode,
//     save: returnNull,
//   } );

})( window.wp );