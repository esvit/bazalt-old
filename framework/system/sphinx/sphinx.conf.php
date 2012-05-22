source Xmlpipe2Source
{
    type = xmlpipe
    xmlpipe_fixup_utf8 = 1
}

<?php foreach ($indexes as $index => $component) { ?>
source <?php echo $index; ?> : Xmlpipe2Source
{
    xmlpipe_command = /usr/local/bin/php <?php echo $xml_pipe_file; ?> <?php echo $component; ?> <?php echo $index; ?> 
}
<?php } ?>

<?php foreach ($indexes as $index => $component) { ?>
index <?php echo $index; ?>
{
    source              = <?php echo $index; ?> 
    path                = <?php echo $index_path ?><?php echo $index; ?> 
    docinfo             = extern
    morphology          = stem_enru, soundex, metaphone
    html_strip          = 1
    min_stemming_len    = 4
    min_word_len        = 1

    charset_type        = utf-8

    charset_table       = 0..9, A..Z->a..z, _, a..z, \
                          U+451->U+435, U+401->U+435, U+410..U+42F->U+430..U+44F, U+430..U+44F

    ignore_chars        = -, U+AD

    
    expand_keywords         = 1 #v 1.10
    index_exact_words       = 1 #only for v 1.10

#    {sphinx_stopwords_file}
#    {sphinx_wordforms_file}
#    {sphinx_exceptions_file}

    preopen = 1
#	inplace_enable = 1
#	inplace_hit_gap = 1M
#	inplace_docinfo_gap = 1M
}
<?php } ?>

indexer
{
    mem_limit = <?php echo $mem_limit; ?>M
}
searchd
{
    # log file
    # searchd run info is logged here
    log = <?php echo $log_file; ?>

    compat_sphinxql_magics          = 0

    seamless_rotate                 = 1

    preopen_indexes                 = 0

    unlink_old                      = 1

    # query log file
    # all the search queries are logged here
    query_log = <?php echo $query_log; ?>

    # client read timeout, seconds
    read_timeout = <?php echo $read_timeout; ?>

    # maximum amount of children to fork
    # useful to control server load
    max_children = <?php echo $max_children; ?>

    # a file which will contain searchd process ID
    # used for different external automation scripts
    # MUST be present
    pid_file = <?php echo $searchd_pid; ?>

    # maximum amount of matches this daemon would retrieve from each index
    # and serve to client
    #
    # this parameter affects per-client memory usage slightly (16 bytes per match)
    # and CPU usage in match sorting phase; so blindly raising it to 1 million
    # is definitely NOT recommended
    #
    # default is 1000 (just like with Google)
    max_matches = <?php echo $max_matches; ?>

}