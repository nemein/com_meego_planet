document.write('<link rel="stylesheet" href="/midgardmvc-static/com_meego_website/meego-theme/jquery-ui-1.8.11.custom.css">');

jQuery(document).ready(function() {
    var voted = {};
    
    var getIconForButton = function(type) {
        switch (type) {
            case 'votesFor':
                return 'ui-icon-plus';
            case 'votesAgainst':
                return 'ui-icon-minus';
        }
    }
    
    var sendVote = function(subject, voteType) {
        var url = '/votes/' + encodeURIComponent(subject) + '/vote/';
        var data = {};
        switch (voteType) {
            case 'votesFor':
                data.vote = 1;
                break;
            case 'votesAgainst':
                data.vote = -1;
                break;
        }

        jQuery.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(updatedVotes) {
                jQuery('[about="' + voted[subject].uri + '"]').find('[property="mgd\\:votesFor"]').text(updatedVotes.votes['1']);
                jQuery('[about="' + voted[subject].uri + '"]').find('[property="mgd\\:votesAgainst"]').text(updatedVotes.votes['-1']);
            },
            error: function(req) {
                if (req.status === 401) {
                    // User is not logged in, relocate to login form
                    window.location = '/mgd:login?redirect=' + encodeURIComponent(window.location + '#' + subject + '/' + voteType);
                }
            }
        });
    }
    
    var replaceVoteWithRadio = function(subject, type, element) {
        var elementId, widget;
        
        elementId = subject + type;
        widget = jQuery('<input name="' + subject + '" id="' + elementId + '" type="radio" />');
        jQuery(element).before(widget);
        widget.after(jQuery('<label for="' + elementId + '">' + type + '</label>'));
        
        jQuery('[property="mgd\\:userVote"]', widget.parent()).each(function() {
            if (type === "votesFor" &&
                jQuery(this).text() === "1") {
                widget.attr('checked', 'checked');   
            }
            if (type === "votesAgainst" &&
                jQuery(this).text() === "-1") {
                widget.attr('checked', 'checked');   
            }
        });
        
        widget.button({
            icons: {
                primary: getIconForButton(type)
            },
            text: false
        });
        
        widget.click(function() {
            sendVote(subject, type);
        });
        
        return jQuery(element).text();
    }

    jQuery('[about][typeof="sioc:Post"]').each(function() {
        var subject, votesForId, votesAgainstId;
        subject = jQuery('[property="rdfs\\:seeAlso"]', this).attr('content').replace('urn:uuid:', '');
        voted[subject] = {
            uri: jQuery(this).attr('about')
        };
        voted[subject].for = replaceVoteWithRadio(subject, 'votesFor', jQuery('[property="mgd\\:votesFor"]', this));
        voted[subject].against = replaceVoteWithRadio(subject, 'votesAgainst', jQuery('[property="mgd\\:votesAgainst"]', this));
    });
    
    if (window.location.hash &&
        window.location.hash.indexOf('/') !== -1) {
        var components = window.location.hash.split('/');
        sendVote(components[0].replace('#', ''), components[1]);
        window.location.hash = '';
    }
});
