<<?php ?>?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach ($urls as $url => $info) { ?>
    <url>
        <loc><?php echo $url; ?></loc>
        <?php if (isset($info['changefreq'])) { ?><changefreq><?php echo $info['changefreq']; ?></changefreq><?php } ?>

        <?php if (isset($info['priority'])) { ?><priority><?php echo str_replace(',', '.', $info['priority']); ?></priority><?php } ?>

        <news:news>
            <news:publication>
                <news:name><?php echo CMS_Bazalt::getSite()->title; ?></news:name>
                <news:language><?php echo $language; ?></news:language>
            </news:publication>
            <news:publication_date><?php echo $info['lastmod']; ?></news:publication_date>
            <news:title><?php echo $info['title']; ?></news:title>
            <news:keywords><?php echo $info['keywords']; ?></news:keywords>
        </news:news>
        <?php foreach ($info['images'] as $image) { ?>
        <image:image>
            <image:loc><?php echo $image; ?></image:loc>
        </image:image>
        <?php } ?>

    </url>
<?php } ?>
</urlset>