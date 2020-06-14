(function() {
  var Idle;

  if (!document.addEventListener) {
    if (document.attachEvent) {
      document.addEventListener = function(event, callback, useCapture) {
        return document.attachEvent("on" + event, callback, useCapture);
      };
    } else {
      document.addEventListener = function() {
        return {};
      };
    }
  }

  if (!document.removeEventListener) {
    if (document.detachEvent) {
      document.removeEventListener = function(event, callback) {
        return document.detachEvent("on" + event, callback);
      };
    } else {
      document.removeEventListener = function() {
        return {};
      };
    }
  }

  "use strict";

  Idle = {};

  Idle = (function() {
    Idle.isAway = false;

    Idle.awayTimeout = 3000;

    Idle.awayTimestamp = 0;

    Idle.awayWarningTimestamp = 0;

    Idle.awayTimer = null;

    Idle.awayWarning = null;

    Idle.onAway = null;

    Idle.onWarn   = false;

    Idle.onAwayBack = null;

    Idle.onVisible = null;

    Idle.onHidden = null;

    Idle.onWarning = null;

    Idle.warningTimer = null;

    function Idle(options) {
      var activeMethod, activity;
      if (options) {
        this.awayTimeout = parseInt(options.awayTimeout, 10);
        this.onAway = options.onAway;
        this.onWarning = options.onWarning;
        this.onAwayBack = options.onAwayBack;
        this.onVisible = options.onVisible;
        this.warningTimer = options.warningTimer || 30000;
        this.onHidden = options.onHidden;
      }
      activity = this;
      activeMethod = function() {
        return activity.onActive();
      };
      window.addEventListener('click', activeMethod);
      window.addEventListener('mousemove', activeMethod);
      window.addEventListener('mouseenter', activeMethod);
      window.addEventListener('keydown', activeMethod);
      window.addEventListener('scroll', activeMethod);
      window.addEventListener('mousewheel', activeMethod);
      window.addEventListener('touchmove', activeMethod);
      window.addEventListener('touchstart', activeMethod);
    }

    Idle.prototype.onActive = function() {
      this.awayTimestamp = new Date().getTime() + this.awayTimeout;
      if (this.isAway) {
        if (this.onAwayBack) {
          this.onAwayBack();
        }
        this.start();
      }
      this.isAway = false;

      this.awayWarningTimestamp = new Date().getTime() + ( this.awayTimeout - this.warningTimer );
      
      if (this.onWarn) {
        this.warn();
      }
      this.onWarn = false;
      return true;
    };

    Idle.prototype.warn = function()
    {
       var activity;
       this.awayWarningTimestamp = new Date().getTime() + ( this.awayTimeout - this.warningTimer );

       if( this.awayWarning !== null ) 
      {
        clearTimeout(this.awayWarning);
      }
       activity = this;
      this.awayWarning = setTimeout((function() {
       return activity.checkWarning();
      }), this.awayTimeout - this.warningTimer);

      return this;
    }

    Idle.prototype.start = function() {
      var activity;
      if (!this.listener) {
        this.listener = (function() {
          return activity.handleVisibilityChange();
        });
        document.addEventListener("visibilitychange", this.listener, false);
        document.addEventListener("webkitvisibilitychange", this.listener, false);
        document.addEventListener("msvisibilitychange", this.listener, false);
      }

      this.awayTimestamp = new Date().getTime() + this.awayTimeout;
      
      if (this.awayTimer !== null) {
        clearTimeout(this.awayTimer);
      }
      activity = this;
      this.awayTimer = setTimeout((function() {
        return activity.checkAway();
      }), this.awayTimeout + 100);


      this.warn();
    };

    Idle.prototype.checkWarning = function()
    {
        var activity, t;
        t = new Date().getTime();

        if (t < this.awayWarningTimestamp) {
          this.onWarn = false;
          activity = this;
          this.awayWarning = setTimeout((function() {
            return activity.checkWarning();
          }), ( this.awayWarningTimestamp - t ) - this.warningTimer);
          return;
        }

        if (this.awayWarning !== null) {
          clearTimeout(this.awayWarning);
        }

        this.onWarn = true;
        if (this.onWarning) {
          return this.onWarning();
        }
    }

    Idle.prototype.stop = function() {
      if (this.awayTimer !== null) {
        clearTimeout(this.awayTimer);
      }

      if (this.awayWarning !== null) {
        clearTimeout(this.awayWarning);
      }
      if (this.listener !== null) {
        document.removeEventListener("visibilitychange", this.listener);
        document.removeEventListener("webkitvisibilitychange", this.listener);
        document.removeEventListener("msvisibilitychange", this.listener);
        this.listener = null;
      }
      return this;
    };

    Idle.prototype.setAwayTimeout = function(ms) {
      this.awayTimeout = parseInt(ms, 10);
      return this;
    };

    Idle.prototype.checkAway = function() {
      var activity, t;
      t = new Date().getTime();
      if (t < this.awayTimestamp) {
        this.isAway = false;
        activity = this;
        this.awayTimer = setTimeout((function() {
          return activity.checkAway();
        }), this.awayTimestamp - t + 100);
        return;
      }
      if (this.awayTimer !== null) {
        clearTimeout(this.awayTimer);
      }
      this.isAway = true;
      if (this.onAway) {
        return this.onAway();
      }
    };

    Idle.prototype.handleVisibilityChange = function() {
      if (document.hidden || document.msHidden || document.webkitHidden) {
        if (this.onHidden) {
          return this.onHidden();
        }
      } else {
        if (this.onVisible) {
          return this.onVisible();
        }
      }
    };

    return Idle;

  })();

  if (typeof define === 'function' && define.amd) {
    define([], Idle);
  } else if (typeof exports === 'object') {
    module.exports = Idle;
  } else {
    window.Idle = Idle;
  }

}).call(this);