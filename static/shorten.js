jQuery(document).ready(function() {
    var captionize = function(element) {
        var newLinize = ['<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>'];
        jQuery.each(newLinize, function(tag) {
            element.html(element.html().replace(tag, "\n" + tag));
        });
        return element.text().substring(0, 400);
    }

    var allowedLength = 1000;
    jQuery('[about][typeof="sioc:Post"] [property="sioc\\:content"]', this).each(function() {
        var postElement = jQuery(this);
        if (postElement.text().length < allowedLength) {
            return true;
        }
        
        var totalLength = 0;
        var currentLength = 0;
        postElement.children().each(function() {
            var elementLength = jQuery(this).text().length;
            if (totalLength + elementLength > allowedLength)
            {
                jQuery(this).addClass('planetoverflow');
            }
            else
            {
                currentLength += elementLength;
            }
            totalLength += elementLength;
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
