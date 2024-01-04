/*
  @Name: layui 经典模块化前端 UI 框架
  @Homepage: www.layui.com
  @Author: 贤心
  @License：MIT
*/

(function (win) {
  'use strict';

  let doc = document;
  let config = {
    modules: {},
    status: {},
    timeout: 10,
    event: {}
  };
  let Layui = function () {
    this.v = '2.5.6';
  };
  let getPath = (function () {
    let jsPath = doc.currentScript ? doc.currentScript.src : (function () {
      let js = doc.scripts;
      let last = js.length - 1;
      let src;
      for (let i = last; i > 0; i--) {
        if (js[i].readyState === 'interactive') {
          src = js[i].src;
          break;
        }
      }
      return src || js[last].src;
    }());
    return jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
  }());
  let error = function (msg) {
    // win.console && console.error && console.error('Layui hint: ' + msg);
  };
  let isOpera = typeof opera !== 'undefined' && opera.toString() === '[object Opera]';
  let modules = {
    form: 'form',
    date: 'date'
  };

  Layui.prototype.onevent = function (modName, events, callback) {
    if (typeof modName !== 'string' || typeof callback !== 'function') return this;
    return Layui.event(modName, events, null, callback);
  };
  Layui.prototype.define = function (deps, factory) {
    let that = this;
    let type = typeof deps === 'function';
    let callback = function () {
      let setApp = function (app, exports) {
        layui[app] = exports;
        config.status[app] = true;
      };
      typeof factory === 'function' && factory(function (app, exports) {
        setApp(app, exports);
      });
      return this;
    };
    if (type) {
      factory = deps;
      deps = [];
    }
    that.use(deps, callback);
    return that;
  };

  Layui.prototype.use = function (apps, callback, exports) {
    let that = this;
    let dir = config.dir = config.dir ? config.dir : getPath;
    let head = doc.getElementsByTagName('head')[0];
    apps = typeof apps === 'string' ? [apps] : apps;

    if (window.jQuery && jQuery.fn.on) {
      that.each(apps, function (index, item) {
        if (item === 'jquery') {
          apps.splice(index, 1);
        }
      });
      layui.jquery = layui.$ = jQuery;
    }

    let item = apps[0];
    let timeout = 0;
    exports = exports || [];

    config.host = config.host || (dir.match(/\/\/([\s\S]+?)\//) || ['//' + location.host + '/'])[0];

    function onScriptLoad (e, url) {
      let readyRegExp = navigator.platform === 'PLaySTATION 3' ? /^complete$/ : /^(complete|loaded)$/;
      if (e.type === 'load' || (readyRegExp.test((e.currentTarget || e.srcElement).readyState))) {
        config.modules[item] = url;
        head.removeChild(node);
        (function poll () {
          if (++timeout > config.timeout * 1000 / 4) {
            return error(item + ' is not a valid module');
          }
          config.status[item] ? onCallback() : setTimeout(poll, 4);
        }());
      }
    }

    function onCallback () {
      exports.push(layui[item]);
      apps.length > 1
        ? that.use(apps.slice(1), callback, exports)
        : (typeof callback === 'function' && callback.apply(layui, exports));
    }

    if (apps.length === 0 ||
    (layui['layui.all'] && modules[item]) ||
    (!layui['layui.all'] && layui['layui.mobile'] && modules[item])
    ) {
      onCallback();
      return that;
    }

    let url = (modules[item] ? (dir) : (/^{\/}/.test(that.modules[item]) ? '' : (config.base || ''))) + (that.modules[item] || item) + '.js';
    url = url.replace(/^{\/}/, '');

    if (!config.modules[item] && layui[item]) {
      config.modules[item] = url;
    }

    if (!config.modules[item]) {
      var node = doc.createElement('script');
      node.async = true;
      node.charset = 'utf-8';
      node.src = url + (function () {
        let version = config.version === true ? (config.v || (new Date()).getTime()) : (config.version || '');
        return version ? ('?v=' + version) : '';
      }());

      head.appendChild(node);

      if (node.attachEvent && !(node.attachEvent.toString && node.attachEvent.toString().indexOf('[native code') < 0) && !isOpera) {
        node.attachEvent('onreadystatechange', function (e) {
          onScriptLoad(e, url);
        });
      } else {
        node.addEventListener('load', function (e) {
          onScriptLoad(e, url);
        }, false);
      }
      config.modules[item] = url;
    } else {
      (function poll () {
        if (++timeout > config.timeout * 1000 / 4) {
          return error(item + ' is not a valid module');
        }
        (typeof config.modules[item] === 'string' && config.status[item])
          ? onCallback()
          : setTimeout(poll, 4);
      }());
    }
    return that;
  };

  Layui.prototype.modules = (function () {
    let clone = {};
    for (let o in modules) {
      clone[o] = modules[o];
    }
    return clone;
  }());

  Layui.prototype.hint = function () {
    return {
      error: error
    };
  };

  Layui.prototype.each = function (obj, fn) {
    let key;
    let that = this;
    if (typeof fn !== 'function') return that;
    obj = obj || [];
    if (obj.constructor === Object) {
      for (key in obj) {
        if (obj.hasOwnProperty(key) && fn.call(obj[key], key, obj[key])) break;
      }
    } else {
      for (key = 0; key < obj.length; key++) {
        if (fn.call(obj[key], key, obj[key])) break;
      }
    }
    return that;
  };

  Layui.prototype.event = Layui.event = function (modName, events, params, fn) {
    let that = this;
    let result = null;
    let filter = events.match(/\((.*)\)$/) || [];
    let eventName = (modName + '.' + events).replace(filter[0], '');
    let filterName = filter[1] || '';
    let callback = function (_, item) {
      let res = item && item.call(that, params);
      res === false && result === null && (result = false);
    };

    if (fn) {
      config.event[eventName] = config.event[eventName] || {};
      config.event[eventName][filterName] = [fn];
      return this;
    }

    layui.each(config.event[eventName], function (key, item) {
      if (filterName === '{*}') {
        layui.each(item, callback);
        return;
      }
      key === '' && layui.each(item, callback);
      (filterName && key === filterName) && layui.each(item, callback);
    });

    return result;
  };

  Layui.prototype.cache = config;

  win.layui = new Layui();
}(window));

$(function () {
  layui.use(['form'], function () {
    layui.form.render('select');
  });
});
