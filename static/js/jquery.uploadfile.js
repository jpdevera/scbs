! function(e) {
    var r = e("#base_url").val() + "static/js/";
    void 0 == e.fn.ajaxForm && e.getScript(r + "jquery.form.js");
    var t = {};
    t.fileapi = void 0 !== e("<input type='file'/>").get(0).files, t.formdata = void 0 !== window.FormData, e.fn.uploadFile = function(r) {
        function a() {
            v.afterUploadAll && !b && (b = !0, function e() {
                0 != w.sCounter && w.sCounter + w.fCounter == w.tCounter ? (v.afterUploadAll(w), b = !1) : window.setTimeout(e, 100)
            }())
        }

        function o(r, t, a) {
            a.on("dragenter", function(r) {
                r.stopPropagation(), r.preventDefault(), e(this).addClass(t.dragDropHoverClass)
            }), a.on("dragover", function(r) {
                r.stopPropagation(), r.preventDefault();
                var a = e(this);
                a.hasClass(t.dragDropContainerClass) && !a.hasClass(t.dragDropHoverClass) && a.addClass(t.dragDropHoverClass)
            }), a.on("drop", function(a) {
                a.preventDefault(), e(this).removeClass(t.dragDropHoverClass), r.errorLog.html("");
                var o = a.originalEvent.dataTransfer.files;
                l(t, r, o);
                return !t.multiple && o.length > 1 ? void(t.showError && e("<div class='" + t.errorClass + "'>" + t.multiDragErrorStr + "</div>").appendTo(r.errorLog)) : void( 0 != t.onSelect(o, t))
            }), a.on("dragleave", function(r) {
                e(this).removeClass(t.dragDropHoverClass)
            }), e(document).on("dragenter", function(e) {
                e.stopPropagation(), e.preventDefault()
            }), e(document).on("dragover", function(r) {
                r.stopPropagation(), r.preventDefault();
                var a = e(this);
                a.hasClass(t.dragDropContainerClass) || a.removeClass(t.dragDropHoverClass)
            }), e(document).on("drop", function(r) {
                r.stopPropagation(), r.preventDefault(), e(this).removeClass(t.dragDropHoverClass)
            })
        }

        function s(e) {
            var r = "",
                t = e / 1024;
            if (parseInt(t) > 1024) {
                var a = t / 1024;
                r = a.toFixed(2) + " MB"
            } else r = t.toFixed(2) + " KB";
            return r
        }

        function i(r) {
            var t = [];
            t = "string" == jQuery.type(r) ? r.split("&") : e.param(r).split("&");
            var a, o, s = t.length,
                i = [];
            for (a = 0; s > a; a++) t[a] = t[a].replace(/\+/g, " "), o = t[a].split("="), i.push([decodeURIComponent(o[0]), decodeURIComponent(o[1])]);
            return i
        }

        function l(r, t, a) {
            for (var o = 0; o < a.length; o++)
                if (n(t, r, a[o].name))
                    if (r.allowDuplicates || !d(t, a[o].name))
                        if (-1 != r.maxFileSize && a[o].size > r.maxFileSize) r.showError && e("<div class='" + r.errorClass + "'><b>" + a[o].name + "</b> " + r.sizeErrorStr + s(r.maxFileSize) + "</div>").appendTo(t.errorLog);
                        else if (-1 != r.maxFileCount && t.selectedFiles >= r.maxFileCount) r.showError && e("<div class='" + r.errorClass + "'><b>" + a[o].name + "</b> " + r.maxFileCountErrorStr + r.maxFileCount + "</div>").appendTo(t.errorLog);
            else {
                t.selectedFiles++, t.existingFileNames.push(a[o].name);
                var l = r,
                    u = new FormData,
                    p = r.fileName.replace("[]", "");
                u.append(p, a[o]);
                var c = r.formData;
                if (c)
                    for (var h = i(c), v = 0; v < h.length; v++) h[v] && u.append(h[v][0], h[v][1]);
                l.fileData = u;
                var g = new f(t, r),
                    w = "";
                w = r.showFileCounter ? t.fileCounter + r.fileCounterStyle + a[o].name : a[o].name, g.filename.html(w);
                var C = e("<form style='display:block; position:absolute;left: 150px;' class='" + t.formGroup + "' method='" + r.method + "' action='" + r.url + "' enctype='" + r.enctype + "'></form>");
                C.appendTo("body");
                var b = [],
                    x = r.uniqueName[t.fileCounter - 1] || "",
                    D = [];
                
                var extra_html  = r.extraHTML.call(null, a[o].name);
                g.statusbar.append(extra_html);
                
                b.push(a[o].name), D.push(x), m(C, l, g, b, t, a[o], x, D), t.fileCounter++;

            } else r.showError && e("<div class='" + r.errorClass + "'><b>" + a[o].name + "</b> " + r.duplicateErrorStr + "</div>").appendTo(t.errorLog);
            else r.showError && e("<div class='" + r.errorClass + "'><b>" + a[o].name + "</b> " + r.extErrorStr + r.allowedTypes + "</div>").appendTo(t.errorLog)
        }

        function n(e, r, t) {
            var a = r.allowedTypes.toLowerCase().split(","),
                o = t.split(".").pop().toLowerCase();
            return "*" != r.allowedTypes && jQuery.inArray(o, a) < 0 ? !1 : !0
        }

        function d(e, r) {
            var t = !1;
            if (e.existingFileNames.length)
                for (var a = 0; a < e.existingFileNames.length; a++)(e.existingFileNames[a] == r || v.duplicateStrict && e.existingFileNames[a].toLowerCase() == r.toLowerCase()) && (t = !0);
            return t
        }

        function u(e, r, t) {
            if (e.existingFileNames.length)
                for (var a = 0; a < r.length; a++) {
                    var t = e.existingFileNames.indexOf(r[a]); - 1 != t && e.existingFileNames.splice(t, 1)
                }
        }

        function p(e, r) {
            if (e) {
                r.show();
                var t = new FileReader;
                t.onload = function(e) {
                    r.attr("src", e.target.result)
                }, t.readAsDataURL(e)
            }
        }

        function c(r, t) {
            if (r.showFileCounter) {
                var a = e(".ajax-file-upload-filename").length;
                t.fileCounter = a + 1, e(".ajax-file-upload-filename").each(function(t, o) {
                    var s = e(this).html().split(r.fileCounterStyle),
                        i = (parseInt(s[0]) - 1, a + r.fileCounterStyle + s[1]);
                    e(this).html(i), a--
                })
            }
        }

        function h(r, a, o, s) {
            var i = "ajax-upload-id-" + (new Date).getTime(),
                d = e("<form method='" + o.method + "' action='" + o.url + "' enctype='" + o.enctype + "'></form>"),
                u = "<input type='file' id='" + i + "' name='" + o.fileName + "' accept='" + o.acceptFiles + "'/>";
            o.multiple && (o.fileName.indexOf("[]") != o.fileName.length - 2 && (o.fileName += "[]"), u = "<input type='file' id='" + i + "' name='" + o.fileName + "' accept='" + o.acceptFiles + "' multiple/>");
            var p = e(u).appendTo(d);
            p.change(function() {
                r.errorLog.html("");
                var i = (o.allowedTypes.toLowerCase().split(","), []);

                if (c(o, r), s.unbind("click"), d.hide(), h(r, a, o, s), d.addClass(a), t.fileapi && t.formdata) {
                    d.removeClass(a);
                    var v = this.files;
                    l(o, r, v)
                } else {
                    for (var g = "", w = 0; w < i.length; w++) g += o.showFileCounter ? r.fileCounter + o.fileCounterStyle + i[w] + "<br>" : i[w] + "<br>", r.fileCounter++;
                    if (-1 != o.maxFileCount && r.selectedFiles + i.length > o.maxFileCount) return void(o.showError && e("<div class='" + o.errorClass + "'><b>" + g + "</b> " + o.maxFileCountErrorStr + o.maxFileCount + "</div>").appendTo(r.errorLog));
                    r.selectedFiles += i.length;
                    var C = new f(r, o);
                    
                    C.filename.html(g), m(d, o, C, i, r, null)
                }

               if (this.files) {
                    for (w = 0; w < this.files.length; w++) i.push(this.files[w].name);
                    if (0 == o.onSelect(this.files, o)) return
                } else {
                    var u = e(this).val(),
                        p = [];
                    if (i.push(u), !n(r, o, u)) return void(o.showError && e("<div class='" + o.errorClass + "'><b>" + u + "</b> " + o.extErrorStr + o.allowedTypes + "</div>").appendTo(r.errorLog));
                    if (p.push({
                            name: u,
                            size: "NA"
                        }), 0 == o.onSelect(p, o)) return
                }
            }), o.nestedForms ? (d.css({
                margin: 0,
                padding: 0
            }), s.css({
                position: "relative",
                overflow: "hidden",
                cursor: "default"
            }), p.css({
                position: "absolute",
                cursor: "pointer",
                top: "0px",
                width: "100%",
                height: "100%",
                left: "0px",
                "z-index": "100",
                opacity: "0.0",
                filter: "alpha(opacity=0)",
                "-ms-filter": "alpha(opacity=0)",
                "-khtml-opacity": "0.0",
                "-moz-opacity": "0.0"
            }), d.appendTo(s)) : (d.appendTo(e("body")), d.css({
                margin: 0,
                padding: 0,
                display: "block",
                position: "absolute",
                left: "-250px"
            }), -1 != navigator.appVersion.indexOf("MSIE ") ? s.attr("for", i) : s.click(function() {
                p.click()
            }))
        }

        function f(r, t) {

            this.statusbar = e("<div class='ajax-file-upload-statusbar'></div>").width(t.statusBarWidth);
            this.preview = e("<img class='ajax-file-upload-preview' />").width(t.previewWidth).height(t.previewHeight).appendTo(this.statusbar).hide();
            this.filename = e("<div class='ajax-file-upload-filename'></div>").appendTo(this.statusbar);
            this.progressDiv = e("<div class='ajax-file-upload-progress'>").appendTo(this.statusbar).hide();
            this.progressbar = e("<div class='ajax-file-upload-bar " + r.formGroup + "'></div>").appendTo(this.progressDiv);
            this.abort = e("<div class='ajax-file-upload-red " + t.abortButtonClass + " " + r.formGroup + "'>" + t.abortStr + "</div>").appendTo(this.statusbar).hide();
            this.cancel = e("<div class='ajax-file-upload-red " + t.cancelButtonClass + " " + r.formGroup + "'><input type='hidden' value='"+t.cancelButtonClass+"'>" + t.cancelStr + "</div>").appendTo(this.statusbar).hide();
            this.done = e("<div class='ajax-file-upload-green'>" + t.doneStr + "</div>").appendTo(this.statusbar).hide();
            this.download = e("<div class='ajax-file-upload-green'>" + t.downloadStr + "</div>").appendTo(this.statusbar).hide();
            this.del = e("<div class='ajax-file-upload-red'>" + t.deletelStr + "</div>").appendTo(this.statusbar).hide();
            this.clear = e("<div style='clear:both'></div>").appendTo(this.statusbar);
            
            if( t.showQueueDiv )
            {
                e("#" + t.showQueueDiv).append(this.statusbar);
            }
            else
            {

                if( r.next().next('div.ajax-file-upload-statusbar').length == 0 )
                {
                    r.errorLog.after( this.statusbar );
                }
                else
                {
                    r.closest('div.field-multi-attachment,div.main-upload-div').find("div.ajax-file-upload-statusbar").last().after( this.statusbar );
                }

                // r.errorLog.after( this.statusbar );
            }

            // t.showQueueDiv ? e("#" + t.showQueueDiv).append(this.statusbar) : r.errorLog.after(this.statusbar);

            return this;
        }

        function m(r, o, s, l, n, d, h, f) {
            n.checkUploaded = !0;
            var m = {
                cache: !1,
                contentType: !1,
                processData: !1,
                forceSync: !1,
                type: o.method,
                data: o.formData,
                formData: o.fileData,
                dataType: o.returnType,
                beforeSubmit: function(e, t, d) {
                    if (0 != o.onSubmit.call(this, l)) {
                        var p = o.dynamicFormData();
                        if (p) {
                            var h = i(p);
                            if (h)
                                for (var m = 0; m < h.length; m++) h[m] && (void 0 != o.fileData ? d.formData.append(h[m][0], h[m][1]) : d.data[h[m][0]] = h[m][1])
                        }
                        return n.tCounter += l.length, a(), !0
                    }
                    return s.statusbar.append("<div class='" + o.errorClass + "'>" + o.uploadErrorStr + "</div>"), s.cancel.show(), r.remove(), s.cancel.click(function() {
                        var file    = $(this).find('input').val();
                        var file_c  = file.split('ajax-file-upload-cancel ');
                        var f_arr   = [];

                        f_arr.push( file_c[1] );
                        u(n, l), s.statusbar.remove(), o.onCancel.call(n, l, s, f_arr), n.selectedFiles -= l.length, c(o, n)
                    }), !1
                },
                beforeSend: function(e, r) {
                    s.progressDiv.show(), s.cancel.hide(), s.done.hide(), o.showAbort && (s.abort.show(), s.abort.click(function() {
                        u(n, l), e.abort(), n.selectedFiles -= l.length
                    })), t.formdata ? s.progressbar.width("1%") : s.progressbar.width("5%")
                },
                uploadProgress: function(e, r, t, a) {
                    0 != o.onProgress.call(this, l), a > 98 && (a = 98);
                    var i = a + "%";
                    a > 1 && s.progressbar.width(i), o.showProgress && (s.progressbar.html(i), s.progressbar.css("text-align", "center"))
                },
                success: function(t, a, i) {
                    if ("json" == o.returnType && "object" == e.type(t) && t.hasOwnProperty(o.customErrorKeyStr)) {
                        s.abort.hide();
                        var d = t[o.customErrorKeyStr];
                        return o.onError.call(this, l, 200, d, s), o.showStatusAfterError ? (s.progressDiv.hide(), s.statusbar.append("<span class='" + o.errorClass + "'>ERROR: " + d + "</span>")) : (s.statusbar.hide(), s.statusbar.remove()), n.selectedFiles -= l.length, r.remove(), void(n.fCounter += l.length)
                    }
                    n.responses.push(t), s.progressbar.width("100%"), o.showProgress && (s.progressbar.html("100%"), s.progressbar.css("text-align", "center")), s.abort.hide(), o.onSuccess.call(this, l, t, i, s, o), o.showStatusAfterSuccess ? (o.showDone ? (s.done.show(), s.done.click(function() {
                        s.statusbar.hide("slow"), s.statusbar.remove()
                    })) : s.done.hide(), o.showDelete ? (s.del.show(), s.del.click(function() {
                        var e = confirm("Are you sure you want to delete " + t + "?");
                        e && (s.statusbar.hide().remove(), o.deleteCallback && o.deleteCallback.call(this, t, s), n.selectedFiles -= l.length, c(o, n))
                    })) : s.del.hide()) : (s.statusbar.hide("slow"), s.statusbar.remove()), o.showDownload && (s.download.show(), s.download.click(function() {
                        o.downloadCallback && o.downloadCallback(t)
                    })), r.remove(), n.sCounter += l.length
                },
                error: function(e, t, a) {
                    s.abort.hide(), "abort" == e.statusText ? (s.statusbar.hide("slow").remove(), c(o, n)) : (o.onError.call(this, l, t, a, s), o.showStatusAfterError ? (s.progressDiv.hide(), s.statusbar.append("<span class='" + o.errorClass + "'>ERROR: " + a + "</span>")) : (s.statusbar.hide(), s.statusbar.remove()), n.selectedFiles -= l.length), r.remove(), n.fCounter += l.length
                }
            };
            
            o.showPreview && null != d && "image" == d.type.toLowerCase().split("/").shift() && p(d, s.preview), o.autoSubmit ? r.ajaxSubmit(m) : (o.showCancel && (s.cancel.show(), s.cancel.click(function() {
                var file    = $(this).find('input').val();
                var file_c  = file.split('ajax-file-upload-cancel ');
                var f_arr   = [];

                f_arr.push( file_c[1] );

                u(n, l, o), r.remove(), s.statusbar.remove(), o.onCancel.call(n, l, s, f_arr), n.selectedFiles -= l.length, c(o, n)
            })), r.ajaxForm(m))
        }
        var v = e.extend({
            url: "",
            method: "POST",
            enctype: "multipart/form-data",
            returnType: null,
            allowDuplicates: !0,
            duplicateStrict: !1,
            allowedTypes: "*",
            acceptFiles: "*",
            fileName: "file",
            formData: {},
            uniqueName: [],
            uniqueNamereal : [],
            dynamicFormData: function() {
                return {}
            },
            maxFileSize: -1,
            maxFileCount: -1,
            multiple: !0,
            dragDrop: !0,
            autoSubmit: !0,
            showCancel: !0,
            showAbort: !0,
            showDone: !0,
            showDelete: !1,
            showError: !0,
            showStatusAfterSuccess: !0,
            showStatusAfterError: !0,
            showFileCounter: !1,
            fileCounterStyle: "). ",
            showProgress: !1,
            nestedForms: !0,
            showDownload: !1,
            onLoad: function(e, v) {},
            onSelect: function(e, r) {
                return !0
            },
            onSubmit: function(e, r) {},
            onProgress: function(e, r) {},
            onSuccess: function(e, r, t, a, o) {},
            onError: function(e, r, t, a) {},
            onCancel: function(e, r, t) {},
            downloadCallback: !1,
            deleteCallback: !1,
            afterUploadAll: !1,
            abortButtonClass: "ajax-file-upload-abort",
            cancelButtonClass: "ajax-file-upload-cancel",
            dragDropContainerClass: "ajax-upload-dragdrop",
            dragDropHoverClass: "state-hover",
            errorClass: "ajax-file-upload-error",
            uploadButtonClass: "ajax-file-upload",
            dragDropStr: "<div class='ajax-dragdrop-label'><h5>Drag & Drop</h5>your files here or upload them manually</div>",
            abortStr: "Abort",
            cancelStr: "Cancel",
            deletelStr: "Delete",
            doneStr: "Done",
            multiDragErrorStr: "Multiple File Drag & Drop is not allowed.",
            extErrorStr: "is not allowed. Allowed extensions: ",
            duplicateErrorStr: "is not allowed. File already exists.",
            sizeErrorStr: "is not allowed. Allowed Max size: ",
            uploadErrorStr: "Upload is not allowed",
            maxFileCountErrorStr: " is not allowed. Maximum no. of allowed files are: ",
            downloadStr: "Download",
            customErrorKeyStr: "jquery-upload-file-error",
            showQueueDiv: !1,
            statusBarWidth: 602,
            dragdropWidth: 600,
            showPreview: !1,
            previewHeight: "auto",
            previewWidth: "100%",
            uploadFolder: "uploads/",
            extraHTML: function(filename){}
        }, r);
        this.fileCounter = 1, this.selectedFiles = 0, this.fCounter = 0, this.sCounter = 0, this.tCounter = 0, this.checkUploaded = !1;
        var g = "ajax-file-upload-" + (new Date).getTime();
        this.formGroup = g, this.hide(), this.errorLog = e("<div></div>"), this.after(this.errorLog), this.responses = [], this.existingFileNames = [], t.formdata || (v.dragDrop = !1), t.formdata || (v.multiple = !1);
        var w = this,
            C = e("<div>" + e(this).html() + "</div>");
        e(C).addClass(v.uploadButtonClass),
            function x() {
                if (e.fn.ajaxForm) {
                    if (v.dragDrop) {
                        var r = e('<div class="' + v.dragDropContainerClass + '" style="vertical-align:top;"></div>').width(v.dragdropWidth);
                        e(w).before(r), e(r).append(e(v.dragDropStr)), e(r).append(C), o(w, v, r)
                    } else e(w).before(C);   
                    v.onLoad.apply(this, [w, v]), h(w, g, v, C)
                } else window.setTimeout(x, 10)
            }(), this.startUpload = function() {
                e("." + this.formGroup).each(function(r, t) {
                    e(this).is("form") && e(this).submit()
                })
            }, this.getFileCount = function() {
                return w.selectedFiles
            }, this.stopUpload = function() {
                e("." + v.abortButtonClass).each(function(r, t) {
                    e(this).hasClass(w.formGroup) && e(this).click()
                })
            }, this.cancelAll = function() {
                e("." + v.cancelButtonClass).each(function(r, t) {
                    e(this).hasClass(w.formGroup) && e(this).click()
                })
            }, this.update = function(r) {
                v = e.extend(v, r)
            }, this.createProgress = function(e) {
                var r = new f(this, v);
                r.progressDiv.show(), r.progressbar.width("100%");
                var t = "";
                t = v.showFileCounter ? w.fileCounter + v.fileCounterStyle + e : e, r.filename.html(t), w.fileCounter++, w.selectedFiles++, v.showPreview && (r.preview.attr("src", v.uploadFolder + e), r.preview.show()), v.showDownload && (r.download.show(), r.download.click(function() {
                    v.downloadCallback && v.downloadCallback.call(w, [e])
                })), r.del.show(), r.del.click(function() {
                    var t = confirm("Are you sure you want to delete " + e + "?");
                    if (t) {
                        r.statusbar.hide().remove();
                        var a = [e];
                        v.deleteCallback && v.deleteCallback.call(this, a, r), w.selectedFiles -= 1, c(v, w)
                    }
                })
            }, this.getResponses = function() {
                return this.responses
            }, this.getCheckUploaded = function() {
                return this.checkUploaded
            }, this.recallOnLoad = function(opt) {
                if( opt !== undefined )
                {
                    v.onLoad.apply(this, [w, opt])    
                }
                else
                {
                    v.onLoad.apply(this, [w, {}])       
                }
                
            };
        var b = !1;
        return this
    }
}(jQuery);