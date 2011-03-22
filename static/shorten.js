jQuery(document).ready(function() {
    var captionize = function(element) {
        var textElement = element.clone();
        var newLinize = ['<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>'];
        jQuery.each(newLinize, function(tag) {
            textElement.html(textElement.html().replace(tag, "\n" + tag));
        });
        return textElement.text().substring(0, 400);
    }

    var allowedLength = 1000;
    jQuery('[about][typeof="sioc:Post"] [property="sioc\\:content"]', this).each(function() {
        var postElement = jQuery(this);
        if (postElement.text().length < allowedLength) {
            return true;
        }
        
        var totalLength = 0;
        var currentLength = 0;
        
        var checkElement = function(element) {
            var elementLength = jQuery(element).text().length;
            if (totalLength + elementLength > allowedLength)
            {
                if (element.tagName === 'UL') {
                    jQuery(element).children().each(function() {
                        checkElement(this);
                    });
                }
                else {    
                    jQuery(element).addClass('planetoverflow');
                }
            }
            else
            {
                currentLength += elementLength;
            }
            totalLength += elementLength;
        }
        
        postElement.children().each(function() {
            checkElement(this);
        });

        if (currentLength === 0)
        {
            var plaintextElement = jQuery('<p>' + captionize(jQuery(':first-child', postElement)) + '</p>');
            plaintextElement.addClass('planetoverflowcaption');
            postElement.prepend(plaintextElement);
        }
        
        var showButton = jQuery('<button>Show more</button>').button();
        showButton.click(function() {
            jQuery('.planetoverflow', postElement).removeClass('planetoverflow');
            jQuery('.planetoverflowcaption', postElement).remove();
            showButton.hide();
        });
        postElement.append(showButton);
    });
});
