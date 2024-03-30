(function (c) {
  let g = 'iCheck';
  let e = g + '-helper';
  let q = 'checkbox';
  let a = 'radio';
  let s = 'checked';
  let x = 'un' + s;
  let i = 'disabled';
  let h = 'determinate';
  let b = 'in' + h;
  let r = 'update';
  let t = 'type';
  let d = 'click';
  let w = 'touchbegin.i touchend.i';
  let p = 'addClass';
  let f = 'removeClass';
  let l = 'trigger';
  let z = 'label';
  let o = 'cursor';
  let n = /ipad|iphone|ipod|android|blackberry|windows phone|opera mini|silk/i.test(navigator.userAgent);
  c.fn[g] = function (N, E) {
    let J = 'input[type=' + q + '],input[type=' + a + ']';
    let L = c();
    let B = function (O) {
      O.each(function () {
        let P = c(this);
        if (P.is(J)) {
          L = L.add(P);
        } else {
          L = L.add(P.find(J));
        }
      });
    };
    if (/^(check|uncheck|toggle|indeterminate|determinate|disable|enable|update|destroy)$/i.test(N)) {
      N = N.toLowerCase();
      B(this);
      return L.each(function () {
        let O = c(this);
        if (N === 'destroy') {
          u(O, 'ifDestroyed');
        } else {
          v(O, true, N);
        }
        if (c.isFunction(E)) {
          E();
        }
      });
    } else {
      if (typeof N === 'object' || !N) {
        let F = c.extend({checkedClass: s, disabledClass: i, indeterminateClass: b, labelHover: true}, N);
        let G = F.handle;
        let I = F.hoverClass || 'hover';
        let M = F.focusClass || 'focus';
        // let K = F.activeClass || 'active';
        let C = !!F.labelHover;
        let H = F.labelHoverClass || 'hover';
        let D = ('' + F.increaseArea).replace('%', '') | 0;
        if (G === q || G === a) {
          J = 'input[type=' + G + ']';
        }
        if (D < -50) {
          D = -50;
        }
        B(this);
        return L.each(function () {
          let Z = c(this);
          u(Z);
          let R = this;
          let O = R.id;
          let S = -D + '%';
          let aa = 100 + (D * 2) + '%';
          let T = {
            position: 'absolute',
            top: S,
            left: S,
            display: 'block',
            width: aa,
            height: aa,
            margin: 0,
            padding: 0,
            background: '#fff',
            border: 0,
            opacity: 0
          };
          let U = n ? {position: 'absolute', visibility: 'hidden'} : D ? T : {position: 'absolute', opacity: 0};
          let V = R[t] === q ? F.checkboxClass || 'i' + q : F.radioClass || 'i' + a;
          let X = c(z + '[for="' + O + '"]').add(Z.closest(z));
          let W = !!F.aria;
          let Q = g + '-' + Math.random().toString(36).substr(2, 6);
          let Y = '<div class="' + V + '" ' + (W ? 'role="' + R[t] + '" ' : '');
          // let P;
          if (W) {
            X.each(function () {
              Y += 'aria-labelledby="';
              if (this.id) {
                Y += this.id;
              } else {
                this.id = Q;
                Y += Q;
              }
              Y += '"';
            });
          }
          Y = Z.wrap(Y + '/>')[l]('ifCreated').parent().append(F.insert);
          // P = c('<ins class="' + e + '"/>').css(T).appendTo(Y);
          Z.data(g, {o: F, s: Z.attr('style')}).css(U);
          !!F.inheritClass && Y[p](R.className || '');
          !!F.inheritID && O && Y.attr('id', g + '-' + O);
          Y.css('position') === 'static' && Y.css('position', 'relative');
          v(Z, true, r);
          if (X.length) {
            X.on(d + '.i mouseover.i mouseout.i ' + w, function (ad) {
              let ab = ad[t];
              let ac = c(this);
              if (!R[i]) {
                if (ab === d) {
                  if (c(ad.target).is('a')) {
                    return;
                  }
                  v(Z, false, true);
                } else {
                  if (C) {
                    if (/ut|nd/.test(ab)) {
                      Y[f](I);
                      ac[f](H);
                    } else {
                      Y[p](I);
                      ac[p](H);
                    }
                  }
                }
                if (n) {
                  ad.stopPropagation();
                } else {
                  return false;
                }
              }
            });
          }
          Z.on(d + '.i focus.i blur.i keyup.i keydown.i keypress.i', function (ad) {
            let ac = ad[t];
            let ab = ad.keyCode;
            if (ac === d) {
              return false;
            } else {
              if (ac === 'keydown' && ab === 32) {
                if (!(R[t] === a && R[s])) {
                  if (R[s]) {
                    y(Z, s);
                  } else {
                    k(Z, s);
                  }
                }
                return false;
              } else {
                if (ac === 'keyup' && R[t] === a) {
                  !R[s] && k(Z, s);
                } else {
                  if (/us|ur/.test(ac)) {
                    Y[ac === 'blur' ? f : p](M);
                  }
                }
              }
            }
          });
          /* P.on(d + ' mousedown mouseup mouseover mouseout ' + w, function (ad) {
            let ac = ad[t];
            let ab = /wn|up/.test(ac) ? K : I;
            if (!R[i]) {
              if (ac === d) {
                v(Z, false, true);
              } else {
                if (/wn|er|in/.test(ac)) {
                  Y[p](ab);
                } else {
                  Y[f](ab + ' ' + K);
                }
                if (X.length && C && ab === I) {
                  X[/ut|nd/.test(ac) ? f : p](H);
                }
              }
              if (n) {
                ad.stopPropagation();
              } else {
                return false;
              }
            }
          }); */
        });
      } else {
        return this;
      }
    }
  };

  function v (B, H, G) {
    let C = B[0];
    let E = /er/.test(G) ? b : /bl/.test(G) ? i : s;
    let F = G === r ? {
      checked: C[s],
      disabled: C[i],
      indeterminate: B.attr(b) === 'true' || B.attr(h) === 'false'
    } : C[E];
    if (/^(ch|di|in)/.test(G) && !F) {
      k(B, E);
    } else {
      if (/^(un|en|de)/.test(G) && F) {
        y(B, E);
      } else {
        if (G === r) {
          for (let D in F) {
            if (F[D]) {
              k(B, D, true);
            } else {
              y(B, D, true);
            }
          }
        } else {
          if (!H || G === 'toggle') {
            if (!H) {
              B[l]('ifClicked');
            }
            if (F) {
              if (C[t] !== a) {
                y(B, E);
              }
            } else {
              k(B, E);
            }
          }
        }
      }
    }
  }

  function k (K, D, B) {
    let G = K[0];
    let M = K.parent();
    let L = D === s;
    let C = D === b;
    let H = D === i;
    let N = C ? h : L ? x : 'enabled';
    let F = m(K, N + j(G[t]));
    let J = m(K, D + j(G[t]));
    if (G[D] !== true) {
      if (!B && D === s && G[t] === a && G.name) {
        let E = K.closest('form');
        let I = 'input[name=' + G.name + ']';
        I = E.length ? E.find(I) : c(I);
        I.each(function () {
          if (this !== G && c(this).data(g)) {
            y(c(this), D);
          }
        });
      }
      if (C) {
        G[D] = true;
        if (G[s]) {
          y(K, s, 'force');
        }
      } else {
        if (!B) {
          G[D] = true;
        }
        if (L && G[b]) {
          y(K, b, false);
        }
      }
      A(K, L, D, B);
    }
    if (G[i] && !!m(K, o, true)) {
      M.find('.' + e).css(o, 'default');
    }
    M[p](J || m(K, D) || '');
    if (!!M.attr('role') && !C) {
      M.attr('aria-' + (H ? i : s), 'true');
    }
    M[f](F || m(K, N) || '');
  }

  function y (I, D, B) {
    let F = I[0];
    let K = I.parent();
    let J = D === s;
    let C = D === b;
    let G = D === i;
    let L = C ? h : J ? x : 'enabled';
    let E = m(I, L + j(F[t]));
    let H = m(I, D + j(F[t]));
    if (F[D] !== false) {
      if (C || !B || B === 'force') {
        F[D] = false;
      }
      A(I, J, L, B);
    }
    if (!F[i] && !!m(I, o, true)) {
      K.find('.' + e).css(o, 'pointer');
    }
    K[f](H || m(I, D) || '');
    if (!!K.attr('role') && !C) {
      K.attr('aria-' + (G ? i : s), 'false');
    }
    K[p](E || m(I, L) || '');
  }

  function u (B, C) {
    if (B.data(g)) {
      B.parent().html(B.attr('style', B.data(g).s || ''));
      if (C) {
        B[l](C);
      }
      B.off('.i').unwrap();
      c(z + "[for='" + B[0].id + "']").add(B.closest(z)).off('.i');
    }
  }

  function m (B, D, C) {
    if (B.data(g)) {
      return B.data(g).o[D + (C ? '' : 'Class')];
    }
  }

  function j (B) {
    return B.charAt(0).toUpperCase() + B.slice(1);
  }

  function A (C, D, E, B) {
    if (!B) {
      if (D) {
        C[l]('ifToggled');
      }
      C[l]('ifChanged')[l]('if' + j(E));
    }
  }
})(window.jQuery);
