/*
  @Name: layDate 日期时间控件
  @Author: 贤心
  @Homepage: www.layui.com
  @License：MIT
*/

(function () {
  'use strict';

  let laydate = {
    v: '5.0.9',
    config: {},
    index: 0,
    path: false,
    set: function (options) {
      let that = this;
      that.config = lay.extend({}, that.config, options);
      return that;
    },
    ready: function (fn) {
      if (typeof fn === 'function') fn();
      return this;
    }
  };
  let thisDate = function () {
    let that = this;
    return {
      hint: function (content) {
        that.hint.call(that, content);
      },
      config: that.config
    };
  };

  let THIS = 'this';
  let DISABLED = 'disabled';
  let LIMIT_YEAR = [100, 200000];
  let ELEM_STATIC = 'static';
  let ELEM_LIST = 'list';
  let ELEM_HINT = 'hint';
  let ELEM_CONFIRM = '.buttons-confirm';
  let ELEM_TIME_TEXT = 'time-text';
  let ELEM_TIME_BTN = '.buttons-time';

  let Class = function (options) {
    let that = this;
    that.index = ++laydate.index;
    that.config = lay.extend({}, that.config, laydate.config, options);
    laydate.ready(function () {
      that.init();
    });
  };
  let LAY = function (selector) {
  };
  LAY.prototype = [];
  LAY.prototype.constructor = LAY;

  let lay = function (selector) {
    return new LAY(selector);
  };
  lay.extend = function () {
    let args = arguments;
    let clone = function (target, obj) {
      target = target || (obj.constructor === Array ? [] : {});
      for (let i in obj) {
        if (obj.hasOwnProperty(i)) target[i] = (obj[i] && (obj[i].constructor === Object)) ? clone(target[i], obj[i]) : obj[i];
      }
      return target;
    };
    args[0] = typeof args[0] === 'object' ? args[0] : {};
    for (let i = 1; i < args.length; i++) {
      if (typeof args[i] === 'object') {
        clone(args[0], args[i]);
      }
    }
    return args[0];
  };
  lay.digit = function (num, length) {
    let str = '';
    num = String(num);
    length = length || 2;
    for (let i = num.length; i < length; i++) {
      str += '0';
    }
    return num < Math.pow(10, length) ? str + (num | 0) : num;
  };
  lay.elem = function (elemName, attr) {
    let elem = document.createElement(elemName);
    $.each(attr || {}, function (key, value) {
      elem.setAttribute(key, value);
    });
    return elem;
  };
  LAY.addStr = function (str, newStr) {
    str = str.replace(/\s+/, ' ');
    newStr = newStr.replace(/\s+/, ' ').split(' ');
    $.each(newStr, function (i, item) {
      if (!new RegExp('\\b' + item + '\\b').test(str)) str = str + ' ' + item;
    });
    return str.replace(/^\s|\s$/, '');
  };
  LAY.removeStr = function (str, newStr) {
    str = str.replace(/\s+/, ' ');
    newStr = newStr.replace(/\s+/, ' ').split(' ');
    $.each(newStr, function (i, item) {
      let exp = new RegExp('\\b' + item + '\\b');
      if (exp.test(str)) str = str.replace(exp, '');
    });
    return str.replace(/\s+/, ' ').replace(/^\s|\s$/, '');
  };

  /* 组件操作 */
  Class.isLeapYear = function (year) {
    return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
  };
  Class.prototype.config = {
    type: 'date', // 控件类型，支持：year/month/date/time/datetime
    range: false, // 是否开启范围选择，即双控件
    format: 'yyyy-MM-dd', // 默认日期格式
    value: null, // 默认日期，支持传入new Date()，或者符合format参数设定的日期格式字符
    isInitValue: true, // 用于控制是否自动向元素填充初始值（需配合 value 参数使用）
    min: '1900-1-1', // 有效最小日期，年月日必须用“-”分割，时分秒必须用“:”分割。注意：它并不是遵循 format 设定的格式。
    max: '2099-12-31', // 有效最大日期，同上
    trigger: 'focus', // 呼出控件的事件
    show: false, // 是否直接显示，如果设置true，则默认直接显示控件
    showBottom: true, // 是否显示底部栏
    buttons: ['clear', 'now', 'confirm'], // 右下角显示的按钮，会按照数组顺序排列
    lang: 'cn', // 语言，只支持cn/en，即中文和英文
    theme: 'default', // 主题
    position: null, // 控件定位方式定位, 默认absolute，支持：fixed/absolute/static
    calendar: false, // 是否开启公历重要节日，仅支持中文版
    zIndex: null, // 控件层叠顺序
    done: null, // 控件选择完毕后的回调，点击清空/现在/确定也均会触发
    change: null // 日期时间改变后的回调
  };
  Class.prototype.lang = function () {
    return {
      weeks: ['日', '一', '二', '三', '四', '五', '六'],
      time: ['时', '分', '秒'],
      timeTips: '选择时间',
      startTime: '开始时间',
      endTime: '结束时间',
      dateTips: '返回日期',
      month: ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二'],
      tools: {
        confirm: '确定',
        clear: '清空',
        now: '现在'
      }
    };
  };
  Class.prototype.init = function () {
    let that = this;
    let options = that.config;
    let dateType = 'yyyy|y|MM|M|dd|d|HH|H|mm|m|ss|s';
    let isStatic = options.position === 'static';
    let format = {
      year: 'yyyy',
      month: 'yyyy-MM',
      date: 'yyyy-MM-dd',
      time: 'HH:mm:ss',
      datetime: 'yyyy-MM-dd HH:mm:ss'
    };
    options.elem = $(options.elem);
    options.eventElem = $(options.eventElem);
    if (!options.elem[0]) return;
    if (options.range === true) options.range = '-';
    if (options.format === format.date) options.format = format[options.type];
    that.format = options.format.match(new RegExp(dateType + '|.', 'g')) || [];
    that.EXP_IF = '';
    that.EXP_SPLIT = '';
    $.each(that.format, function (i, item) {
      item = item.toString();
      let EXP = new RegExp(dateType).test(item) ? '\\d{' + (function () {
        if (new RegExp(dateType).test(that.format[i === 0 ? i + 1 : i - 1] || '')) {
          if (/^yyyy|y$/.test(item)) return 4;
          return item.length;
        }
        if (/^yyyy$/.test(item)) return '1,4';
        if (/^y$/.test(item)) return '1,308';
        return '1,2';
      }()) + '}' : '\\' + item;
      that.EXP_IF = that.EXP_IF + EXP;
      that.EXP_SPLIT = that.EXP_SPLIT + '(' + EXP + ')';
    });
    that.EXP_IF = new RegExp('^' + (options.range ? that.EXP_IF + '\\s\\' + options.range + '\\s' + that.EXP_IF : that.EXP_IF) + '$');
    that.EXP_SPLIT = new RegExp('^' + that.EXP_SPLIT + '$', '');
    if (!options.elem.attr('lay-key')) {
      options.elem.attr('lay-key', that.index);
      options.eventElem.attr('lay-key', that.index);
    }
    $.each(['min', 'max'], function (i, item) {
      let ymd = (options[item].match(/\d+-\d+-\d+/) || [''])[0].split('-');
      let hms = (options[item].match(/\d+:\d+:\d+/) || [''])[0].split(':');
      let date = new Date();
      options[item] = {
        year: ymd[0] | 0 || date.getFullYear(),
        month: ymd[1] ? (ymd[1] | 0) - 1 : date.getMonth(),
        date: ymd[2] | 0 || date.getDate(),
        hours: hms[0] | 0,
        minutes: hms[1] | 0,
        seconds: hms[2] | 0
      };
    });
    that.elemID = 'layui-date' + options.elem.attr('lay-key');
    if (options.show || isStatic) that.render();
    isStatic || that.events();
    if (options.value && options.isInitValue) that.setValue(options.value.constructor === Date ? that.parse(0, that.systemDate(options.value)) : options.value);
  };
  Class.prototype.render = function () {
    let that = this;
    let options = that.config;
    let lang = that.lang();
    let isStatic = options.position === 'static';
    let elem = that.elem = lay.elem('div', {
      id: that.elemID,
      'class': [
        'layui-date',
        options.range ? ' range' : '',
        isStatic ? (' ' + ELEM_STATIC) : '',
        options.theme && options.theme !== 'default' && !/^#/.test(options.theme) ? (' theme-' + options.theme) : ''
      ].join('')
    });
    let elemMain = that.elemMain = [];
    that.elemHeader = [];
    that.elemCont = [];
    that.table = [];
    let divFooter = that.footer = lay.elem('div', {'class': 'footer'});
    if (options.zIndex) elem.style.zIndex = options.zIndex;
    $.each(new Array(2), function (i) {
      if (!options.range && i > 0) return true;
      let divHeader = lay.elem('div', {'class': 'layui-date-header'});
      let headerChild = [
        lay.elem('i', {'class': 'layui-font previous-year'}),
        lay.elem('i', {'class': 'layui-font previous-month'}),
        (function () {
          let elem = lay.elem('div', {'class': 'set-ym'});
          elem.appendChild(lay.elem('span'));
          elem.appendChild(lay.elem('span'));
          return elem;
        }()),
        lay.elem('i', {'class': 'layui-font next-month'}),
        lay.elem('i', {'class': 'layui-font next-year'})
      ];
      let divContent = lay.elem('div', {'class': 'content'});
      let table = lay.elem('table');
      let thead = lay.elem('thead');
      let theadTr = lay.elem('tr');
      $.each(headerChild, function (i, item) {
        divHeader.appendChild(item);
      });
      thead.appendChild(theadTr);
      $.each(new Array(6), function (i) {
        let tr = table.insertRow(0);
        $.each(new Array(7), function (j) {
          if (i === 0) {
            let th = lay.elem('th');
            th.innerHTML = lang.weeks[j];
            theadTr.appendChild(th);
          }
          tr.insertCell(j);
        });
      });
      table.insertBefore(thead, table.children[0]);
      divContent.appendChild(table);
      elemMain[i] = lay.elem('div', {'class': 'layui-date-main main-list-' + i});
      elemMain[i].appendChild(divHeader);
      elemMain[i].appendChild(divContent);
      that.elemHeader.push(headerChild);
      that.elemCont.push(divContent);
      that.table.push(table);
    });
    $(divFooter).html(function () {
      let html = [];
      let button = [];
      if (options.type === 'datetime') html.push('<span lay-type="datetime" class="buttons-time">' + lang.timeTips + '</span>');
      $.each(options.buttons, function (i, item) {
        let title = lang.tools[item] || 'btn';
        if (options.range && item === 'now') return;
        if (isStatic && item === 'clear') title = options.lang === 'cn' ? '重置' : 'Reset';
        button.push('<span lay-type="' + item + '" class="buttons-' + item + '">' + title + '</span>');
      });
      html.push('<div class="buttons">' + button.join('') + '</div>');
      html.push('<p style="clear:both;"></p>');
      return html.join('');
    }());
    $.each(elemMain, function (i, main) {
      elem.appendChild(main);
    });
    options.showBottom && elem.appendChild(divFooter);
    that.remove(Class.thisElemDate);
    document.body.appendChild(elem);
    that.position();
    that.checkDate().calendar();
    that.changeEvent();
    Class.thisElemDate = that.elemID;
    typeof options.ready === 'function' && options.ready(lay.extend({}, options.dateTime, {
      month: options.dateTime.month + 1
    }));
  };
  Class.prototype.remove = function (prev) {
    let elem = $('#' + (prev || this.elemID));
    if (!elem.hasClass(ELEM_STATIC)) {
      this.checkDate(function () {
        elem.remove();
      });
    }
    return this;
  };

  // 定位算法
  Class.prototype.position = function () {
    let that = this;
    let options = that.config;
    let elem = that.bindElem || options.elem[0];
    let rect = elem.getBoundingClientRect();
    let elemWidth = that.elem.offsetWidth;
    let elemHeight = that.elem.offsetHeight;
    let scrollArea = function (type) {
      type = type ? 'scrollLeft' : 'scrollTop';
      return document.body[type] | document.documentElement[type];
    };
    let winArea = function (type) {
      return document.documentElement[type ? 'clientWidth' : 'clientHeight'];
    };
    let margin = 5;
    let left = rect.left;
    let top = rect.bottom;
    if (left + elemWidth + margin > winArea('width')) left = winArea('width') - elemWidth - margin;
    if (top + elemHeight + margin > winArea()) {
      top = rect.top > elemHeight ? rect.top - elemHeight : winArea() - elemHeight;
      top = top - margin * 2;
    }
    if (options.position) that.elem.style.position = options.position;
    that.elem.style.left = left + (options.position === 'fixed' ? 0 : scrollArea(1)) + 'px';
    that.elem.style.top = top + (options.position === 'fixed' ? 0 : scrollArea()) + 'px';
  };
  Class.prototype.hint = function (content) {
    let that = this;
    let div = lay.elem('div', {'class': ELEM_HINT});
    if (!that.elem) return;
    div.innerHTML = content || '';
    $(that.elem).find('.' + ELEM_HINT).remove();
    that.elem.appendChild(div);
    clearTimeout(that.hinTimer);
    that.hinTimer = setTimeout(function () {
      $(that.elem).find('.' + ELEM_HINT).remove();
    }, 3000);
  };
  Class.prototype.getAsYM = function (Y, M, type) {
    type ? M-- : M++;
    if (M < 0) {
      M = 11;
      Y--;
    }
    if (M > 11) {
      M = 0;
      Y++;
    }
    return [Y, M];
  };
  Class.prototype.systemDate = function (newDate) {
    let thisDate = newDate || new Date();
    return {
      year: thisDate.getFullYear(),
      month: thisDate.getMonth(),
      date: thisDate.getDate(),
      hours: newDate ? newDate.getHours() : 0,
      minutes: newDate ? newDate.getMinutes() : 0,
      seconds: newDate ? newDate.getSeconds() : 0
    };
  };
  Class.prototype.checkDate = function (fn) {
    let that = this;
    let options = that.config;
    let dateTime = options.dateTime = options.dateTime || that.systemDate();
    let thisMaxDate;
    let error;
    let elem = that.bindElem || options.elem[0];
    let value = that.isInput(elem) ? elem.value : (options.position === 'static' ? '' : elem.innerHTML);
    let checkValid = function (dateTime) {
      if (dateTime.year > LIMIT_YEAR[1]) {
        dateTime.year = LIMIT_YEAR[1];
        error = true;
      }
      if (dateTime.month > 11) {
        dateTime.month = 11;
        error = true;
      }
      if (dateTime.hours > 23) {
        dateTime.hours = 0;
        error = true;
      }
      if (dateTime.minutes > 59) {
        dateTime.minutes = 0;
        dateTime.hours++;
        error = true;
      }
      if (dateTime.seconds > 59) {
        dateTime.seconds = 0;
        dateTime.minutes++;
        error = true;
      }
      thisMaxDate = laydate.getEndDate(dateTime.month + 1, dateTime.year);
      if (dateTime.date > thisMaxDate) {
        dateTime.date = thisMaxDate;
        error = true;
      }
    };
    let initDate = function (dateTime, value, index) {
      let startEnd = ['startTime', 'endTime'];
      value = (value.match(that.EXP_SPLIT) || []).slice(1);
      index = index || 0;
      if (options.range) {
        that[startEnd[index]] = that[startEnd[index]] || {};
      }
      $.each(that.format, function (i, item) {
        item = item.toString();
        let thisValue = parseFloat(value[i]);
        if (value[i].length < item.length) error = true;
        if (/yyyy|y/.test(item)) {
          if (thisValue < LIMIT_YEAR[0]) {
            thisValue = LIMIT_YEAR[0];
            error = true;
          }
          dateTime.year = thisValue;
        } else if (/MM|M/.test(item)) {
          if (thisValue < 1) {
            thisValue = 1;
            error = true;
          }
          dateTime.month = thisValue - 1;
        } else if (/dd|d/.test(item)) {
          if (thisValue < 1) {
            thisValue = 1;
            error = true;
          }
          dateTime.date = thisValue;
        } else if (/HH|H/.test(item)) {
          if (thisValue < 1) {
            thisValue = 0;
            error = true;
          }
          dateTime.hours = thisValue;
          options.range && (that[startEnd[index]].hours = thisValue);
        } else if (/mm|m/.test(item.toString())) {
          if (thisValue < 1) {
            thisValue = 0;
            error = true;
          }
          dateTime.minutes = thisValue;
          options.range && (that[startEnd[index]].minutes = thisValue);
        } else if (/ss|s/.test(item.toString())) {
          if (thisValue < 1) {
            thisValue = 0;
            error = true;
          }
          dateTime.seconds = thisValue;
          options.range && (that[startEnd[index]].seconds = thisValue);
        }
      });
      checkValid(dateTime);
    };
    if (fn === 'limit') {
      checkValid(dateTime);
      return that;
    }
    value = value || options.value;
    if (typeof value === 'string') value = value.replace(/\s+/g, ' ').replace(/^\s|\s$/g, '');
    if (that.startState && !that.endState) {
      delete that.startState;
      that.endState = true;
    }
    if (typeof value === 'string' && value) {
      if (that.EXP_IF.test(value)) {
        if (options.range) {
          value = value.split(' ' + options.range + ' ');
          that.startDate = that.startDate || that.systemDate();
          that.endDate = that.endDate || that.systemDate();
          options.dateTime = lay.extend({}, that.startDate);
          $.each([that.startDate, that.endDate], function (i, item) {
            initDate(item, value[i], i);
          });
        } else {
          initDate(dateTime, value);
        }
      } else {
        that.hint('日期格式不合法<br>必须遵循下述格式：<br>' + (options.range ? (options.format + ' ' + options.range + ' ' + options.format) : options.format) + '<br>已为你重置');
        error = true;
      }
    } else if (value && value.constructor === Date) {
      options.dateTime = that.systemDate(value);
    } else {
      options.dateTime = that.systemDate();
      delete that.startState;
      delete that.endState;
      delete that.startDate;
      delete that.endDate;
      delete that.startTime;
      delete that.endTime;
    }
    checkValid(dateTime);
    if (error && value) that.setValue(options.range ? (that.endDate ? that.parse() : '') : that.parse());
    fn && fn();
    return that;
  };
  Class.prototype.limit = function (elem, date, index, time) {
    let that = this;
    let options = that.config;
    let timestamp = {};
    let dateTime = options[index > 41 ? 'endDate' : 'dateTime'];
    let isOut;
    let thisDateTime = lay.extend({}, dateTime, date || {});
    $.each({now: thisDateTime, min: options.min, max: options.max}, function (key, item) {
      timestamp[key] = that.newDate(lay.extend(
        {year: item.year, month: item.month, date: item.date},
        (function () {
          let hms = {};
          $.each(time, function (i, keys) {
            hms[keys] = item[keys];
          });
          return hms;
        }())
      )).getTime();
    });
    isOut = timestamp.now < timestamp.min || timestamp.now > timestamp.max;
    elem && elem[isOut ? 'addClass' : 'removeClass'](DISABLED);
    return isOut;
  };
  Class.prototype.calendar = function (value) {
    let that = this;
    let options = that.config;
    let dateTime = value || options.dateTime;
    let date = new Date();
    let startWeek;
    let prevMaxDate;
    let thisMaxDate;
    let isAlone = options.type !== 'date' && options.type !== 'datetime';
    let index = value ? 1 : 0;
    let tds = $(that.table[index]).find('td');
    let elemYM = $(that.elemHeader[index][2]).find('span');
    if (dateTime.year < LIMIT_YEAR[0]) {
      dateTime.year = LIMIT_YEAR[0];
      that.hint('最低只能支持到公元' + LIMIT_YEAR[0] + '年');
    }
    if (dateTime.year > LIMIT_YEAR[1]) {
      dateTime.year = LIMIT_YEAR[1];
      that.hint('最高只能支持到公元' + LIMIT_YEAR[1] + '年');
    }
    if (!that.firstDate) that.firstDate = lay.extend({}, dateTime);
    date.setFullYear(dateTime.year, dateTime.month, 1);
    startWeek = date.getDay();
    prevMaxDate = laydate.getEndDate(dateTime.month || 12, dateTime.year);
    thisMaxDate = laydate.getEndDate(dateTime.month + 1, dateTime.year);
    $.each(tds, function (index, item) {
      let YMD = [dateTime.year, dateTime.month];
      let st;
      item = $(item);
      item.removeAttr('class');
      if (index < startWeek) {
        st = prevMaxDate - startWeek + index;
        item.addClass('day-prev');
        YMD = that.getAsYM(dateTime.year, dateTime.month, 'sub');
      } else if (index >= startWeek && index < thisMaxDate + startWeek) {
        st = index - startWeek;
        if (!options.range) {
          st + 1 === dateTime.date && item.addClass(THIS);
        }
      } else {
        st = index - thisMaxDate - startWeek;
        item.addClass('day-next');
        YMD = that.getAsYM(dateTime.year, dateTime.month);
      }
      YMD[1]++;
      YMD[2] = st + 1;
      item.attr('lay-ymd', YMD.join('-')).html(YMD[2]);
    });
    $(elemYM[0]).attr('lay-ym', dateTime.year + '-' + (dateTime.month + 1));
    $(elemYM[1]).attr('lay-ym', dateTime.year + '-' + (dateTime.month + 1));
    $(elemYM[0]).attr('lay-type', 'year').html(dateTime.year + '年');
    $(elemYM[1]).attr('lay-type', 'month').html((dateTime.month + 1) + '月');
    if (isAlone) {
      if (options.range) {
        value ? that.endDate = (that.endDate || {year: dateTime.year + (options.type === 'year' ? 1 : 0), month: dateTime.month + (options.type === 'month' ? 0 : -1)}) : (that.startDate = that.startDate || {year: dateTime.year, month: dateTime.month});
        if (value) {
          that.listYM = [[that.startDate.year, that.startDate.month + 1], [that.endDate.year, that.endDate.month + 1]];
          that.list(options.type, 0).list(options.type, 1);
        }
      }
      if (!options.range) {
        that.listYM = [[dateTime.year, dateTime.month + 1]];
        that.list(options.type, 0);
      }
    }
    if (options.range && !value) {
      let EYM = that.getAsYM(dateTime.year, dateTime.month);
      that.calendar(lay.extend({}, dateTime, {
        year: EYM[0],
        month: EYM[1]
      }));
    }
    if (!options.range) that.limit($(that.footer).find(ELEM_CONFIRM), null, 0, ['hours', 'minutes', 'seconds']);
    return that;
  };
  Class.prototype.list = function (type, index) {
    let that = this;
    let options = that.config;
    let dateTime = options.dateTime;
    let lang = that.lang();
    let isAlone = options.range && options.type !== 'date' && options.type !== 'datetime';
    let ul = lay.elem('ul', {'class': ELEM_LIST + ' ' + ({year: 'year-list', month: 'month-list', time: 'time-list'})[type]});
    let elemHeader = that.elemHeader[index];
    let elemYM = $(elemHeader[2]).find('span');
    let elemCont = that.elemCont[index || 0];
    let haveList = $(elemCont).find('.' + ELEM_LIST)[0];
    let isCN = options.lang === 'cn';
    let text = isCN ? '年' : '';
    let listYM = that.listYM[index] || {};
    let hms = ['hours', 'minutes', 'seconds'];
    let startEnd = ['startTime', 'endTime'][index];
    let setTimeStatus = function () {
      $(ul).find('ol').each(function (i, ol) {
        $(ol).find('li').each(function (ii, li) {
          that.limit($(li), [{
            hours: ii
          }, {
            hours: that[startEnd].hours,
            minutes: ii
          }, {
            hours: that[startEnd].hours,
            minutes: that[startEnd].minutes,
            seconds: ii
          }][i], index, [['hours'], ['hours', 'minutes'], ['hours', 'minutes', 'seconds']][i]);
        });
      });
      if (!options.range) that.limit($(that.footer).find(ELEM_CONFIRM), that[startEnd], 0, ['hours', 'minutes', 'seconds']);
    };
    if (listYM[0] < 1) listYM[0] = 1;
    if (type === 'year') {
      let yearNum;
      let startY = yearNum = listYM[0] - 7;
      if (startY < 1) startY = yearNum = 1;
      $.each(new Array(15), function () {
        let li = lay.elem('li', {'lay-ym': yearNum});
        let ymd = {year: yearNum};
        yearNum === listYM[0] && $(li).addClass(THIS);
        li.innerHTML = yearNum + text;
        ul.appendChild(li);
        if (yearNum < that.firstDate.year) {
          ymd.month = options.min.month;
          ymd.date = options.min.date;
        } else if (yearNum >= that.firstDate.year) {
          ymd.month = options.max.month;
          ymd.date = options.max.date;
        }
        that.limit($(li), ymd, index);
        yearNum++;
      });
      $(elemYM[isCN ? 0 : 1]).attr('lay-ym', (yearNum - 8) + '-' + listYM[1]).html((startY + text) + ' - ' + (yearNum - 1 + text));
    } else if (type === 'month') {
      $.each(new Array(12), function (i) {
        let li = lay.elem('li', {'lay-ym': i});
        let ymd = {year: listYM[0], month: i};
        i + 1 === listYM[1] && $(li).addClass(THIS);
        li.innerHTML = lang.month[i] + (isCN ? '月' : '');
        ul.appendChild(li);
        if (listYM[0] < that.firstDate.year) {
          ymd.date = options.min.date;
        } else if (listYM[0] >= that.firstDate.year) {
          ymd.date = options.max.date;
        }
        that.limit($(li), ymd, index);
      });
      $(elemYM[isCN ? 0 : 1]).attr('lay-ym', listYM[0] + '-' + listYM[1])
        .html(listYM[0] + text);
    } else if (type === 'time') { // 时间列表
      if (options.range) {
        if (!that[startEnd]) {
          that[startEnd] = {
            hours: 0,
            minutes: 0,
            seconds: 0
          };
        }
      } else {
        that[startEnd] = dateTime;
      }
      $.each([24, 60, 60], function (i, item) {
        let li = lay.elem('li');
        let childUL = ['<p>' + lang.time[i] + '</p><ol>'];
        $.each(new Array(item), function (ii) {
          childUL.push('<li' + (that[startEnd][hms[i]] === ii ? ' class="' + THIS + '"' : '') + '>' + lay.digit(ii, 2) + '</li>');
        });
        li.innerHTML = childUL.join('') + '</ol>';
        ul.appendChild(li);
      });
      setTimeStatus();
    }
    if (haveList) elemCont.removeChild(haveList);
    elemCont.appendChild(ul);

    // 年月
    if (type === 'year' || type === 'month') {
      // 显示切换箭头
      $(that.elemMain[index]).addClass('ym-show');

      // 选中
      $(ul).find('li').on('click', function () {
        let ym = $(this).attr('lay-ym') | 0;
        if ($(this).hasClass(DISABLED)) return;

        if (index === 0) {
          dateTime[type] = ym;
          if (isAlone) that.startDate[type] = ym;
          that.limit($(that.footer).find(ELEM_CONFIRM), null, 0);
        } else { // 范围选择
          if (isAlone) { // 非date/datetime类型
            that.endDate[type] = ym;
          } else { // date/datetime类型
            let YM = type === 'year'
              ? that.getAsYM(ym, listYM[1] - 1, 'sub')
              : that.getAsYM(listYM[0], ym, 'sub');
            lay.extend(dateTime, {
              year: YM[0],
              month: YM[1]
            });
          }
        }

        if (options.type === 'year' || options.type === 'month') {
          $(ul).find('.' + THIS).removeClass(THIS);
          $(this).addClass(THIS);

          // 如果为年月选择器，点击了年列表，则切换到月选择器
          if (options.type === 'month' && type === 'year') {
            that.listYM[index][0] = ym;
            isAlone && (that[['startDate', 'endDate'][index]].year = ym);
            that.list('month', index);
          }
        } else {
          that.checkDate('limit').calendar();
          that.closeList();
        }
        options.range || that.done(null, 'change');
        $(that.footer).find(ELEM_TIME_BTN).removeClass(DISABLED);
      });
    } else {
      let span = lay.elem('span', {'class': ELEM_TIME_TEXT});
      let scroll = function () {
        $(ul).find('ol').each(function (i) {
          let $this = $(this);
          $this.scrollTop(30 * (that[startEnd][hms[i]] - 2));
          if ($this.scrollTop <= 0) {
            $this.find('li').each(function (j) {
              if (!$(this).hasClass(DISABLED)) {
                $this.scrollTop(30 * (j - 2));
                return true;
              }
            });
          }
        });
      };
      let haveSpan = $(elemHeader[2]).find('.' + ELEM_TIME_TEXT);
      scroll();
      span.innerHTML = options.range ? [lang.startTime, lang.endTime][index] : lang.timeTips;
      $(that.elemMain[index]).addClass('time-show');
      if (haveSpan[0]) haveSpan.remove();
      elemHeader[2].appendChild(span);
      $(ul).find('ol').each(function (i) {
        let $that = $(this);
        $that.find('li').on('click', function () {
          let $this = $(this);
          let value = this.innerHTML | 0;
          if ($(this).hasClass(DISABLED)) return;
          if (options.range) {
            that[startEnd][hms[i]] = value;
          } else {
            dateTime[hms[i]] = value;
          }
          $that.find('.' + THIS).removeClass(THIS);
          $this.addClass(THIS);
          setTimeStatus();
          scroll();
          (that.endDate || options.type === 'time') && that.done(null, 'change');
        });
      });
    }
    return that;
  };
  Class.prototype.listYM = [];
  Class.prototype.closeList = function () {
    let that = this;
    $.each(that.elemCont, function (index) {
      $(this).find('.' + ELEM_LIST).remove();
      $(that.elemMain[index]).removeClass('ym-show time-show');
    });
    $(that.elem).find('.' + ELEM_TIME_TEXT).remove();
  };
  Class.prototype.parse = function (state, date) {
    let that = this;
    let options = that.config;
    let dateTime = date || (state ? lay.extend({}, that.endDate, that.endTime) : (options.range ? lay.extend({}, that.startDate, that.startTime) : options.dateTime));
    let format = that.format.concat();
    $.each(format, function (i, item) {
      item = item.toString();
      if (/yyyy|y/.test(item)) { // 年
        format[i] = lay.digit(dateTime.year, item.length);
      } else if (/MM|M/.test(item)) { // 月
        format[i] = lay.digit(dateTime.month + 1, item.length);
      } else if (/dd|d/.test(item)) { // 日
        format[i] = lay.digit(dateTime.date, item.length);
      } else if (/HH|H/.test(item)) { // 时
        format[i] = lay.digit(dateTime.hours, item.length);
      } else if (/mm|m/.test(item)) { // 分
        format[i] = lay.digit(dateTime.minutes, item.length);
      } else if (/ss|s/.test(item)) { // 秒
        format[i] = lay.digit(dateTime.seconds, item.length);
      }
    });
    if (options.range && !state) return format.join('') + ' ' + options.range + ' ' + that.parse(1);
    return format.join('');
  };
  Class.prototype.newDate = function (dateTime) {
    dateTime = dateTime || {};
    return new Date(dateTime.year || 1, dateTime.month || 0, parseInt(dateTime.date) || 1, parseInt(dateTime.hours) || 0, parseInt(dateTime.minutes) || 0, parseInt(dateTime.seconds) || 0);
  };
  Class.prototype.setValue = function (value) {
    let options = this.config;
    let elem = this.bindElem || options.elem[0];
    let valType = this.isInput(elem) ? 'val' : 'html';
    options.position === 'static' || $(elem)[valType](value || '');
    return this;
  };
  Class.prototype.done = function (param, type) {
    let options = this.config;
    let start = lay.extend({}, options.dateTime);
    param = param || [this.parse(), start];
    typeof options[type || 'done'] === 'function' && options[type || 'done'].apply(options, param);
    return this;
  };
  Class.prototype.choose = function (td) {
    let that = this;
    let options = that.config;
    let dateTime = options.dateTime;
    let tds = $(that.elem).find('td');
    let YMD = td.attr('lay-ymd').split('-');
    let setDateTime = function (one) {
      one && lay.extend(dateTime, YMD);
      if (options.range) {
        that.startDate ? lay.extend(that.startDate, YMD) : (that.startDate = lay.extend({}, YMD, that.startTime));
        that.startYMD = YMD;
      }
    };
    YMD = {
      year: YMD[0] | 0,
      month: (YMD[1] | 0) - 1,
      date: YMD[2] | 0
    };
    if (td.hasClass(DISABLED)) return;
    if (options.range) {
      $.each(['startTime', 'endTime'], function (i, item) {
        that[item] = that[item] || {hours: 0, minutes: 0, seconds: 0};
      });
      if (that.endState) { // 重新选择
        setDateTime();
        delete that.endState;
        delete that.endDate;
        that.startState = true;
        tds.removeClass(THIS + ' selected');
        td.addClass(THIS);
      } else if (that.startState) { // 选中截止
        td.addClass(THIS);

        that.endDate ? lay.extend(that.endDate, YMD) : (
          that.endDate = lay.extend({}, YMD, that.endTime)
        );

        // 判断是否顺时或逆时选择
        if (that.newDate(YMD).getTime() < that.newDate(that.startYMD).getTime()) {
          let startDate = lay.extend({}, that.endDate, {
            hours: that.startDate.hours,
            minutes: that.startDate.minutes,
            seconds: that.startDate.seconds
          });
          lay.extend(that.endDate, that.startDate, {
            hours: that.endDate.hours,
            minutes: that.endDate.minutes,
            seconds: that.endDate.seconds
          });
          that.startDate = startDate;
        }

        options.showBottom || that.done();
        that.endState = true;
        that.done(null, 'change');
      } else { // 选中开始
        td.addClass(THIS);
        setDateTime();
        that.startState = true;
      }
      $(that.footer).find(ELEM_CONFIRM)[that.endDate ? 'removeClass' : 'addClass'](DISABLED);
    } else if (options.position === 'static') { // 直接嵌套的选中
      setDateTime(true);
      that.calendar().done().done(null, 'change');
    } else if (options.type === 'date') {
      setDateTime(true);
      that.setValue(that.parse()).remove().done();
    } else if (options.type === 'datetime') {
      setDateTime(true);
      that.calendar().done(null, 'change');
    }
  };

  // 底部按钮
  Class.prototype.tool = function (btn, type) {
    let that = this;
    let options = that.config;
    let dateTime = options.dateTime;
    let isStatic = options.position === 'static';
    let active = {
      datetime: function () {
        if ($(btn).hasClass(DISABLED)) return;
        that.list('time', 0);
        options.range && that.list('time', 1);
        $(btn).attr('lay-type', 'date').html(that.lang().dateTips);
      },
      date: function () {
        that.closeList();
        $(btn).attr('lay-type', 'datetime').html(that.lang().timeTips);
      },
      clear: function () {
        that.setValue('').remove();
        if (isStatic) {
          lay.extend(dateTime, that.firstDate);
          that.calendar();
        }
        if (options.range) {
          delete that.startState;
          delete that.endState;
          delete that.endDate;
          delete that.startTime;
          delete that.endTime;
        }
        that.done(['', {}, {}]);
      },
      now: function () {
        let thisDate = new Date();
        lay.extend(dateTime, that.systemDate(), {
          hours: thisDate.getHours(),
          minutes: thisDate.getMinutes(),
          seconds: thisDate.getSeconds()
        });
        that.setValue(that.parse()).remove();
        isStatic && that.calendar();
        that.done();
      },
      confirm: function () {
        if (options.range) {
          if (!that.endDate) return that.hint('请先选择日期范围');
          if ($(btn).hasClass(DISABLED)) {
            return that.hint('开始日期超出了结束' + (options.type === 'time' ? '时间' : '日期') + '<br>建议重新选择');
          }
        } else {
          if ($(btn).hasClass(DISABLED)) return that.hint('不在有效日期或时间范围内');
        }
        that.done();
        that.setValue(that.parse()).remove();
      }
    };
    active[type] && active[type]();
  };
  Class.prototype.change = function (index) {
    let that = this;
    let options = that.config;
    let dateTime = options.dateTime;
    let isAlone = options.range && (options.type === 'year' || options.type === 'month');
    let elemCont = that.elemCont[index || 0];
    let listYM = that.listYM[index];
    let addSubYear = function (type) {
      let startEnd = ['startDate', 'endDate'][index];
      let isYear = $(elemCont).find('.year-list')[0];
      let isMonth = $(elemCont).find('.month-list')[0];
      if (isYear) {
        listYM[0] = type ? listYM[0] - 15 : listYM[0] + 15;
        that.list('year', index);
      }
      if (isMonth) {
        type ? listYM[0]-- : listYM[0]++;
        that.list('month', index);
      }
      if (isYear || isMonth) {
        lay.extend(dateTime, {year: listYM[0]});
        if (isAlone) that[startEnd].year = listYM[0];
        options.range || that.done(null, 'change');
        options.range || that.limit($(that.footer).find(ELEM_CONFIRM), {year: listYM[0]});
      }
      return isYear || isMonth;
    };
    return {
      prevYear: function () {
        if (addSubYear('sub')) return;
        dateTime.year--;
        that.checkDate('limit').calendar();
        options.range || that.done(null, 'change');
      },
      prevMonth: function () {
        let YM = that.getAsYM(dateTime.year, dateTime.month, 'sub');
        lay.extend(dateTime, {
          year: YM[0],
          month: YM[1]
        });
        that.checkDate('limit').calendar();
        options.range || that.done(null, 'change');
      },
      nextMonth: function () {
        let YM = that.getAsYM(dateTime.year, dateTime.month);
        lay.extend(dateTime, {
          year: YM[0],
          month: YM[1]
        });
        that.checkDate('limit').calendar();
        options.range || that.done(null, 'change');
      },
      nextYear: function () {
        if (addSubYear()) return;
        dateTime.year++;
        that.checkDate('limit').calendar();
        options.range || that.done(null, 'change');
      }
    };
  };
  Class.prototype.changeEvent = function () {
    let that = this;
    $(that.elem).on('click', function (e) {
      e.stopPropagation();
    });
    $.each(that.elemHeader, function (i, header) {
      $(header[0]).on('click', function () {
        that.change(i).prevYear();
      });
      $(header[1]).on('click', function () {
        that.change(i).prevMonth();
      });
      $(header[2]).find('span').on('click', function () {
        let $this = $(this);
        let layYM = $this.attr('lay-ym');
        let layType = $this.attr('lay-type');
        if (!layYM) return;
        layYM = layYM.split('-');
        that.listYM[i] = [layYM[0] | 0, layYM[1] | 0];
        that.list(layType, i);
        $(that.footer).find(ELEM_TIME_BTN).addClass(DISABLED);
      });
      $(header[3]).on('click', function () {
        that.change(i).nextMonth();
      });
      $(header[4]).on('click', function () {
        that.change(i).nextYear();
      });
    });
    $.each(that.table, function (i, table) {
      $(table).find('td').on('click', function () {
        that.choose($(this));
      });
    });
    $(that.footer).find('span').on('click', function () {
      that.tool(this, $(this).attr('lay-type'));
    });
  };
  Class.prototype.isInput = function (elem) {
    return /input|textarea/.test(elem.tagName.toLocaleLowerCase());
  };
  Class.prototype.events = function () {
    let that = this;
    let options = that.config;
    let showEvent = function (elem, bind) {
      elem.on(options.trigger, function () {
        bind && (that.bindElem = this);
        that.render();
        that.position();
      });
    };
    if (!options.elem[0] || options.elem[0].eventHandler) return;
    showEvent(options.elem, 'bind');
    showEvent(options.eventElem);
    $(document).on('click', function (e) {
      if (e.target === options.elem[0] || e.target === options.eventElem[0]) return;
      that.remove();
    });
    $(window).on('resize', function () {
      if (!that.elem || !$('.layui-date')[0]) return false;
      that.position();
    });

    options.elem[0].eventHandler = true;
  };
  laydate.render = function (options) {
    let inst = new Class(options);
    return thisDate.call(inst);
  };
  laydate.getEndDate = function (month, year) {
    let date = new Date();
    date.setFullYear(year || date.getFullYear(), month || (date.getMonth() + 1), 1);
    return new Date(date.getTime() - 1000 * 60 * 60 * 24).getDate();
  };

  laydate.ready();
  layui.define(function (exports) { // layui加载
    laydate.path = layui.cache.dir;
    exports('date', laydate);
  });
}());
