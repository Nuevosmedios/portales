function fixSlideColors()
{
        var ul = $('.ul_container').find('ul');
        var c = 0;
        $(ul).find('li').each(function(index)
        {
                $(this).addClass('color-'+c);
                c++;
        });
}