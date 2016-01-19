if (typeof(orcidee) != 'object'){
    orcidee = {};
}

orcidee.manager = {
    isDebug: false,
    init: function(){
    
        this.additionalCss = "t2012";
        this.list.init();
        this.datePickers();
        this.appControls.init();
        this.dbg();
        if($("#main > .party-create").size() > 0){
            this.createParty.init();
        }
        this.fields();
        this.adaptHeight();
    },
    fields: function(){
        $("textarea").each(function(){
            var limit = parseInt($(this).attr("data-limit"));
            if(typeof limit != 'undefined'){
                $(this).keyup(function(){
                    if($(this).val().length > limit){
                        $(this).val($(this).val().substr(0, limit));
                    }
                });
            }
        });
    },
    adaptHeight: function(){
        var h = $("html").height() + 'px';
        //log(h);
        with(window.parent){
            if(typeof(document.getElementsByTagName("iframe")[0]) != 'undefined'){
                document.getElementsByTagName("iframe")[0].height = h;
            }
        }
    },
    list:{
        init:function(){
            if($("#main > #filteringForm > .list").size() > 0){
				log("trouve la liste !");
                this.dialogBox.init();
                this.actions();
            } else {
				log("pas trouvé la liste");
			}
        },
        actions: function(){
			log("initialise les actions");

            $("#filteringForm select[name='year']").change(function(){

                var year = $(this).val();
                var $animsSelect = $(this).siblings('select[name=animId]');
                log("Change anim list with"+year);

                $.ajax({
                    data:{
                        year: year
                    },
                    url:"actions/animators.php",
                    type:"GET",
                    dataType:"json",
                    success:function(json, s, xhr){
                        log(json);
                        if(json.status == "ok"){
                            // update select
                            $animsSelect.empty();
                            $animsSelect
                                .append($("<option></option>")
                                .attr("value",'')
                                .text('---'));
                            for(i in json.list){
                                var user = json.list[i];
                                $animsSelect
                                    .append($("<option></option>")
                                    .attr("value",user.id)
                                    .text(user.firstName +' '+user.lastName));
                            }

                        } else {
                            log("erreur lors de la récupération des animateur de "+year+" : " + s);
                        }
                    },
                    error: function(xhr, s, e){
                        log("erreur lors de la récupération des animateur de "+year+" : " + s);
                    }
                });

            });

            $(".actions a.cancel").click(function(){
                
                var btn = this;
                var pId = $(this).parent().attr("data-id");
                
                log("Annulation de " + pId );
                
                if (confirm("Voulez-vous vraiment annuler cette partie ?\n(Les éventuels participants seront prévenus par mail)")) {
                    $.ajax({
                        data:{
                            action: 'cancel',
                            partyId: pId
                        },
                        url:"actions/party.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            if(json.status == "ok"){
                                if (confirm("La partie a bien été annulée, et les éventuels participants prévenus.\n\nRafraichir la page ?")){
									document.forms['filteringForm'].reset();
									document.forms['filteringForm'].submit();
								}
                            } else {
								alert("La partie n'a pas pu être annulée !\n("+s+")");
							}
                        },
						error: function(xhr, s, e){
							log("erreur d'annulation : " + s);
                        }
                    });
                }
                return false;
            });
            $(".actions a.refuse").click(function(){
                
                var btn = this;
                var pId = $(this).parent().attr("data-id");
                
                log("Refus de " + pId );
                
                // TODO: Demander la raison avec un dialogbox ou autre.
                
                if (confirm("Voulez-vous vraiment refuser cette partie ?")) {
					log("Refus confirmé ");
                    $.ajax({
                        data:{
                            action: 'refuse',
                            partyId: pId
                        },
                        url:"actions/party.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            /*if(json.status == "ok"){
                                alert("La partie a bien été annulée.");
                            }*/
							document.forms['filteringForm'].reset();
							document.forms['filteringForm'].submit();
                        },
						error: function(xhr, s, e){
							log("erreur de refus : " + s);
						}
                    });
                }
                return false;
            });
            $(".actions a.verify").click(function(){
                
                var btn = this;
                var pId = $(this).parent().attr("data-id");
                
                log("Vérification de " + pId );
                
                if (confirm("Voulez-vous vraiment donner à cette partie le status vérifiée ?")) {
                    $.ajax({
                        data:{
                            action: 'verify',
                            partyId: $(this).parent().attr("data-id")
                        },
                        url:"actions/party.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            /*if(json.status == "ok"){
                                alert("La partie a bien été annulée.");
                            }*/
							document.forms['filteringForm'].reset();
							document.forms['filteringForm'].submit();
                        },
						error: function(xhr, s, e){
							log("erreur de vérification : " + s);
                        }
                    });
                }
                return false;
            });
            $(".actions a.validate").click(function(){
                
                var btn = this;
                var pId = $(this).parent().attr("data-id");
                
                log("Validation de " + pId );
                
                if (confirm("Voulez-vous vraiment valider cette partie ?")) {
                    $.ajax({
                        data:{
                            action: 'validate',
                            partyId: $(this).parent().attr("data-id")
                        },
                        url:"actions/party.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            /*if(json.status == "ok"){
                                alert("La partie a bien été annulée.");
                            }*/
							document.forms['filteringForm'].reset();
							document.forms['filteringForm'].submit();
                        },
						error: function(xhr, s, e){
							log("erreur de validation : " + s);
                        }
                    });
                }
                return false;
            });
            $("ul.players li a.unsubscribe").click(function(){
                
                var btn = this;
                var pId = $(this).parent().parent().attr("data-partyid");
				var playerId = $(this).attr("player-id");
				var playerMail = $(this).attr("player-mail");
				var playerName = $(this).attr("player-name");
                log("désinscription de la partie " + pId + " de l'utilisateur " + playerName + " avec l'email " + playerMail);
                
                if (confirm("Voulez-vous désinscrire " + playerName + " de la partie " + pId + " ?\n(un mail sera envoyé pour confirmer)")) {
                    $.ajax({
                        data:{
                            player_id: playerId,
                            email: playerMail,
                            partyId: pId
                        },
                        url:"actions/unsubscribe.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            if(json.status != "ok"){
								alert("Le mail n'a pas pu être envoyé !\n("+json.message+")");
							}else{
								alert("Un mail a été envoyé, il faut cliquer sur le lien !");
							}
                        },
						error: function(xhr, s, e){
							alert("Le mail n'a pas pu être envoyé !\n("+s+", "+e+")");
							log("erreur de désinscription : " + s);
                        }
                    });
                }
                return false;
            });
            $("ul.players li a.unsubscribeNow").click(function(){
                
                var btn = this;
				var liItem = $(this).parent();
                var pId = $(this).parent().parent().attr("data-partyid");
				var playerCode = $(this).attr("player-code");
				var playerName = $(this).attr("player-name");
                log("désinscription forcée de la partie " + pId + " de l'utilisateur " + playerName);
                
                if (confirm("Etes-vous bien sûr de vouloir désinscrire immédiatement " + playerName + " de la partie " + pId + " ?\n(un mail lui sera envoyé pour l'informer)")) {
                    $.ajax({
                        data:{
                            u: playerCode,
                            partyId: pId,
							admin: 1
                        },
                        url:"actions/unsubscribe.php",
                        type: "GET",
                        dataType: "json",
                        success: function(json, s, xhr){
                            log(json);
                            if(json.status != "ok"){
								alert("Le joueur n'a pas bien été désinscrit\n("+s+")");
							}else{
								liItem.replaceWith("");
							}
                        },
						error: function(xhr, s, e){
							log("erreur de désinscription : " + s);
                        }
                    });
                }
                return false;
            });
        },
        // FIXME: Centrer la lightbox par à la fenetre globale (et non l'iframe)
        dialogBox:{
            init: function(){
				log("initialise la box");
            
				var orc = orcidee.manager;
            
                var me = this;
                me.obj = $("#dialog-form");
                
                me.basic = me.obj.html();
                
                // Initialise jquery.ui.dialog plugin
                me.obj.dialog({
                    autoOpen: false,
                    buttons: [
                        {
                            text: "Fermer",
                            click: function(){
                                $(this).dialog("close");
                            }
                        },
                        {
                            text: "S'inscrire",
                            click: function() {
								log("inscription");
                                
                                var btn = this;
                                var pId = $(this).dialog("option","pId"),
                                    email = $("#email").val(),
                                    lName = $("#lastname").val(),
                                    fName = $("#firstname").val();
                                
                                var valid = true;
                                valid = valid && orc.checkLength( email, 6, 80 );
                                valid = valid && orc.checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "Ton email n'est pas valide." );
                                valid = valid && orc.checkLength( lName, 1, 16 );
                                valid = valid && orc.checkLength( fName, 1, 16 );
                                
                                if(valid){
                                    var data = {
                                        "partyId": pId,
                                        "email": email,
                                        "lastname": lName,
                                        "firstname": fName
                                    };
									
									log("ok : " + pId + ", " + email + ", " + lName + ", " + fName);
									
                                    $.ajax({
                                        data:data,
                                        url:"actions/subscribe.php",
                                        type: "GET",
                                        dataType: "json",
                                        success: function(json, s, xhr){
											//vincent
											log("success");
											if(typeof(json) != 'undefined' && typeof(json.status) != 'undefined'){
												log(json.status);
                                                
												if(json.status == "ok"){
                                                    $("input.subscribe[data-partyId="+pId+"]").replaceWith("<p>Tu es inscrit !</p>");
													$("ul.players[data-partyId="+pId+"]").append("<li>"+fName+" "+lName+"</li>");
                                                }
                                                $("#dialog-form").html("<p>"+json.message+"</p>");
                                                $($(btn).siblings(".ui-dialog-buttonpane").find("button")[1]).hide();
                                            }else{
												log("pb");
                                                
												$("#dialog-form").html("<p>Erreur inconnue</p>");
                                                $($(btn).siblings(".ui-dialog-buttonpane").find("button")[1]).hide();
                                            }
                                        },
										error: function(xhr, s, e){
												$("#dialog-form").html("<p>Erreur inconnue</p>");
                                                $($(btn).siblings(".ui-dialog-buttonpane").find("button")[1]).hide();
												
											log("erreur : " + s);
										}
                                    });
                                }else{
                                    if($("#dialog-form .res").size()>0){
                                        //$("#dialog-form .res").html("");
                                    }else{
                                        $("#dialog-form").append("<p class='res'>Votre saisie n'est pas valide : votre nom doit contenir entre 1 et 17 caractères. Votre adresse email doit être valide.</p>");
                                    }
                                }
                            }
                        }
                    ],
                    dialogClass: orcidee.manager.additionalCss,
                    draggable:false,
                    resizable:false,
                    title:"Confirmation d'inscription",
                    close: function(event, ui){
                        me.obj.html(me.basic);
                        me.obj.parent().find(".ui-dialog-buttonpane button").show();
                    }
                });
                
                // Bind subscribtion buttons
                $("input.subscribe").click(function() {
                    var pId = $(this).attr("data-partyId");
                    var title = $(this).closest("li").find(".main .partyName").html();
                    $(".partyTitle", me.obj).html("Partie ["+pId+"] - &laquo;" +title+"&raquo;");
                    
                    me.obj.dialog("option","pId",pId).dialog( "option", "position", ['center','100'] )
                        .dialog( "option", "modal", true ).dialog("open");
                    
                });
                
            }
        }
    },
    createParty: {

        init: function(){
            var me = this;
            // bind events
            $("#check-dispo").click(function(){
                var pId = ($('#partyId').size() > 0) ? $('#partyId').val() : "";
                me.checkDispo({
                    'duration':$('#duration').val(),
                    'start':$('#start').val(),
                    'partyId':pId,
                    'tableAmount':$('#tableAmount').val()
                }, "click");
            });
            this.checkDispo();
        },
        checkDispo: function(data, event){
            var me = this;
            log(me);
            log(me.options);
            $.ajax({
                data:data,
                url:"actions/check-dispo.php",
                method: "GET",
                dataType: "json",
                success: function(msg, s, xhr){
                    if(msg){
                        if(msg.status == "ok"){
                            log(msg);
                            
                            var html = "<ul id='dispo-preview'>",
                                jour = " 1 - ",
                                time = 0,
                                label = "",
                                charge=0,
                                flip="",
                                state="";

                            var i = 0;
                            for( var slotId in msg.slots){

                                // Current slot (inscriptions in 1 hour)
                                var slot = msg.slots[slotId];
                                
                                // Current hour label
                                time = i + me.options.start;
                                
                                // flipflap is just a css stuff
                                flip = (i % 2 == 0) ? "flip" : "flap";
                                if(time >= 24){
                                    jour = " 2 - ";
                                    time -= 24;
                                }
                                
                                // Current label
                                label = jour + time + "h";
                                
                                // Slot's charge in %
                                charge = slot.length * 100 / me.options.max ;
                                
                                // State of the slot
                                state = (charge > 50) ? ((charge > 75) ? ((charge > 95) ? "black" : "red") : "orange") : "green";
                                
                                html += "<li class='"+flip+"'><div class='h'>"+label+"</div>" +
                                "<div class='bar'><div style='width:"+charge+"%' class='"+state+"'>"+
                                "</div></div></li>";
                                i++;
                            }
                            html += "</ul>"
                        }else{
                            var html = "<p>" + msg.message +"</p>";
                        }
                        $("#check-dispo-result").html(html);
                    }
                    orcidee.manager.adaptHeight();
                }
            });
        }
    },
    datePickers: function(){
        $(".datepicker").each(function(){
            var dp = this;
            $(dp).datepicker({ firstDay: 1, 
							dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
							dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
							monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
							monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jui','Jul','Aou','Sep','Oct','Nov','Déc'] });
            if($(dp).attr("data-selected") != 'undefined'){
                var stamp = $(dp).attr("data-selected");
                if("" != stamp){
                    var d = new Date(stamp*1000);
                    if (typeof(d) == 'object'){
                        $(dp).datepicker("setDate", d);
                    }
                }
            }
        });
    },
    appControls:{
        init: function() {
            var me = this;
            // bind buttons events
            $("#conv-date").click(function(){
                me.saveNewDates(this, $(".convention-date .conv-date")[0]);
            });
            $("#app-dates").click(function(){
                me.saveNewDates(this, $(".application-controls .open-date")[0]);
            });
            $("#mj-dates").click(function(){
                me.saveNewDates(this, $(".mj-controls .open-date")[0]);
            });
            $("#player-dates").click(function(){
                me.saveNewDates(this, $(".player-controls .open-date")[0]);
            });
            $("#number-of-tables-submit").click(function(){
                me.saveNumberOfTables(this, $("#number-of-tables-text"));
            });
        },
        saveNumberOfTables: function(btn, $valueInput){
            $.ajax({
                data:{
                    "value": $valueInput.val(),
                    "action": $valueInput.data("concern")
                },
                url:"actions/controls.php",
                method: "GET",
                dataType: "json",
                success: function(msg, s, xhr){
                    if(msg.status == "ok"){
                        $valueInput.parent().append("New number successfully saved!<br/>");
                    }else{
                        $valueInput.val(msg.oldValue);
                        $valueInput.parent().append("Failed to save new number!<br/>");
                    }
                }
            });
        },
        // Server request to save new dates
        saveNewDates: function(btn){
        
            var $dates = $(btn).siblings(".row").find("[data-dateid]");

            $dates.each(function(){

                var date = $(this).find(".datepicker").datepicker("getDate");

                var year  = date.getFullYear(),
                    month = (date.getMonth() + 1).toString(),
                    day   = (date.getDate()).toString();

                var hours = $(this).find("select")[0],
                    hoursValue = (typeof(hours) == 'undefined')?'00:00':$(hours).val(),
                    dateId = $(this).data("dateid");

                var phpStamp = year + "/" + ((month.length==1)?"0":"") + month + "/" + ((day.length==1)?"0":"") + day + " " + hoursValue;

                $('.info-'+dateId).html("<img src='img/ajax-loader.gif' class='loader' />");

                $.ajax({
                    data:{
                        "stamp": phpStamp,
                        "action": dateId
                    },
                    url:"actions/controls.php",
                    method: "GET",
                    dataType: "json",
                    success: function(msg, s, xhr){
                        if(msg.status == "ok"){
                            $('.info-'+dateId).html(msg.newDate).fadeIn();

                            if(dateId == 'appOpenDate' || dateId == 'appCloseDate'){
                                window.location.reload();
                            }

                        }
                    }
                });

            });

        }
    },
    dbg: function(){
        if((location.href).search(/dbg=true/) != -1){
            $(".dbg").show();
            $("a").each(function(){
                var h = $(this).attr("href");
                var symbol = (h.search(/\?/) != -1) ? "&" : "?" ;
                $(this).attr("href", h + symbol + "dbg=true");
            });
        }
    },
    checkLength: function(text, min, max){
        var res = ( (text.length < max) && (text.length > min) ) ;
        return res;
    },
    checkRegexp: function(text, regexp, error) {
        log("validate: "+text);
        if ( !( regexp.test( text ) ) ) {
            return false;
        } else {
            return true;
        }
    }
};

$(document).ready(function(){
    orcidee.manager.init();
});

function log (msg){
    if(orcidee.manager.isDebug) {
        if (typeof(console.info) == 'function') {
            console.info(msg);
        } else if (typeof(console.log) == 'function') {
            console.log(msg);
        }
    }
}