/*
jQuery Cookie Plugin v1.4.1
https://github.com/carhartl/jquery-cookie
Copyright 2013 Klaus Hartl
Released under the MIT license
*/
(function (factory) {
  factory(jQuery);
}(function ($) {
  let pluses = /\+/g;

  function encode (s) {
    return config.raw ? s : encodeURIComponent(s);
  }
  function decode (s) {
    return config.raw ? s : decodeURIComponent(s);
  }
  function stringifyCookieValue (value) {
    return encode(config.json ? JSON.stringify(value) : String(value));
  }
  function parseCookieValue (s) {
    if (s.indexOf('"') === 0) {
      s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }
    try {
      s = decodeURIComponent(s.replace(pluses, ' '));
      return config.json ? JSON.parse(s) : s;
    } catch (e) {}
  }
  function read (s, converter) {
    let value = config.raw ? s : parseCookieValue(s);
    return $.isFunction(converter) ? converter(value) : value;
  }

  let config = $.cookie = function (key, value, options) {
    if (value !== undefined && !$.isFunction(value)) {
      options = $.extend({}, config.defaults, options);
      if (typeof options.expires === 'number') {
        let t = options.expires = new Date();
        t.setTime(+t + options.expires * 864e+5);
      }
      return (document.cookie = [
        encode(key), '=', stringifyCookieValue(value),
        options.expires ? '; expires=' + options.expires.toUTCString() : '',
        options.path ? '; path=' + options.path : '',
        options.domain ? '; domain=' + options.domain : '',
        options.secure ? '; secure' : ''
      ].join(''));
    }
    let result = key ? undefined : {};
    let cookies = document.cookie ? document.cookie.split('; ') : [];
    for (let i = 0, l = cookies.length; i < l; i++) {
      let parts = cookies[i].split('=');
      let name = decode(parts.shift());
      let cookie = parts.join('=');
      if (key && key === name) {
        result = read(cookie, value);
        break;
      }
      if (!key && (cookie = read(cookie)) !== undefined) result[name] = cookie;
    }
    return result;
  };

  config.defaults = {};

  $.removeCookie = function (key, options) {
    if ($.cookie(key) === undefined) return false;
    $.cookie(key, '', $.extend({}, options, {expires: -1}));
    return !$.cookie(key);
  };
}));
