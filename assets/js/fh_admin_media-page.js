/* Fund Hub Admin JS - Widgets Page */
jQuery(document).ready(function ($) {
  //console.log('FundHub: Custom Media JS Says Hi!');
  //var $images = jQuery('#the-list .media-icon img');
  //console.log($images);
  var search_key = jQuery('.view-list').hasClass('current') ? 's' : 'search';
  var list_mode = search_key === 's';
  //console.log('search_key = ' + search_key);
  var media_admin_url = '';
  var $tree = jQuery('<div class="dir-tree">');
  var $files = list_mode ? jQuery('<div class="dir-files">') : jQuery('.media-frame-content');
  var $media = list_mode ? jQuery('<div class="media-wrap">') : jQuery('.media-frame-tab-panel');
  if (list_mode) {
    $media.append($tree);
    $media.append($files);
    var $files_content = jQuery('.wrap form').first();
    $files_content.before($media);
    $files.append($files_content);
  } else {
    $media.addClass('media-wrap');
    $files.addClass('dir-files');
    $media.prepend($tree);
  }
  function get_dirs( data ) {
    var result = {};
    var baseUrl = data.uploads_url;
    data.links.forEach(function(link) {
      var urlRegex = /(https?:\/\/[^\']*)/;
      var url = link.match(urlRegex)[1];
      var uri = url.replace(baseUrl, '');
      var dirRegex = /^(\/.*\/)/;
      var matches = uri.match(dirRegex);
      if ( matches ) {
        // console.log('matches =', matches);
        var path = matches[1];
        path = path.replace(/^\//, '').replace(/\/$/, '');
        // console.log('path =', path);
        var path_parts = path.split('/');
        // console.log('path_parts =', path_parts);
        var base = result;
        for (var i=0; i < path_parts.length; i++ ) {
          var part = path_parts[ i ];
        //   console.log('part =', part);
          if ( ! base[ part ] ) { base[ part ] = {}; }
        //   console.log('base =', base);
          base = base[ part ];
        //   console.log('result =', result);
        }
      }
    });
    return result;
  }
  function titleCase(str) {
    str = str.toLowerCase().split(' ');
    for (var i = 0; i < str.length; i++) {
      str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1);
    }
    return str.join(' ');
  }
  function pretty( str ) {
    str = str.replace('_', ' ').replace('-', ' ');
    return titleCase( str );
  }
  function searchParam( term ) {
    return search_key + '=' + term;
  }
  function isCurrent( s_term ) {
    s_term = s_term.replace('%2F', '/');
    var regExStr = search_key + '=' + s_term;
    var search = window.location.search;
    console.log('isCurrent search =', search);
    console.log('isCurrent s_term =', s_term);
    var sTermRegex = new RegExp(regExStr);
    var matches = search.match(sTermRegex);
    return matches && matches.length ? ' current' : '';
  }
  function renderDirs( dirs, path_base, level ) {
    level = level || 0;
    if ( level > 3 ) { return; }
    var html = '';
    for ( var dir in dirs ) {
      var path = path_base ? ( path_base + '%2F' + dir ) : dir;
      var url = media_admin_url + searchParam( path );
      html += '<li class="dir' + (level > 0 ? ' L-' + level : ' L-0') +
        isCurrent( path ) + '">' + '<a href="' + url + '">' + pretty( dir ) +
          '</a></li>';
      if ( ! jQuery.isEmptyObject( dirs[ dir ] ) ) {
        html += renderDirs( dirs[ dir ], path , level + 1 );
      }
    }
    //console.log('html =', html);
    return html;
  }
  jQuery.get(window.ajaxurl + '?action=fh_get_media', {}, function( data ) {
    console.log( 'data =', data );
    var dirs = get_dirs( data );
    console.log('dirs =', dirs);
    media_admin_url = data.media_admin_url + '?';
    var html = '<ul><li class="show-all"><a href="' + media_admin_url +
      '">All Files</a></li>';
    html += '<li class="show-docs' + isCurrent('.pdf') + '"><a href="' +
      media_admin_url + searchParam('.pdf') + '">PDF Documents</a></li>';
    html += renderDirs( dirs );
    $tree.html(html);
  }, 'json' );
});