/* Fund Hub JS */

/* globals F1 */

window.F1 = window.F1 || { afterPageLoadScripts: [] };

/**
 * F1.Back2Top - Scroll "Back to top", auto show, floating button.
 *   - When the user scrolls down 40px from the top of the document,
 *     show the button
 *
 * @auth:  C. Moller <xavier.tnc@gmail.com>
 * @date:  14 July 2019
 * @updated: 17 Dec 2020
 *
 */

F1.elDoc = document.documentElement || document.body; 

F1.Back2Top = function (elementSelector, showOffset)
{
  this.active = false;
  this.showOffset = showOffset || 40;
  this.el = document.querySelector(elementSelector || '#back-to-top');
  window.addEventListener('scroll', this.scrollHandler.bind(this));
  console.log('F1 Back2Top Initialized');
};


F1.Back2Top.prototype.scrollHandler = function()
{
  if (F1.elDoc.scrollTop > this.showOffset)
  {
    if (!this.active) {
      F1.elDoc.classList.add('b2t-on');
      this.active = true;
    }
  }
  else {
    if (this.active) {
      F1.elDoc.classList.remove('b2t-on');
      this.active = false;
    }
  }
};

window.scrollTo(0,0);

// F1.elDoc.classList.add('loading');

// setTimeout( function() { F1.elDoc.classList.remove('loading'); }, 2000 );

F1.back2Top = new F1.Back2Top();