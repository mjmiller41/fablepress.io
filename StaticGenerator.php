<?php
// FablePress Static Site Generator

require_once __DIR__ . '/config.php';

class StaticGenerator {
    
    /**
     * Renders all templates and writes them to static HTML files.
     * Also cleans up obsolete static directories of unpublished/deleted stories.
     * 
     * @return array Summary of the generation results.
     */
    public static function generateAll() {
        $results = [
            'success' => true,
            'rendered' => [],
            'cleaned' => [],
            'errors' => []
        ];
        
        try {
            $db = get_db_connection();
            
            // 1. Render Public Homepage -> /index.html
            $homeHtml = self::renderTemplate(__DIR__ . '/public-home.php');
            if (file_put_contents(__DIR__ . '/index.html', $homeHtml) === false) {
                throw new Exception("Failed to write static homepage /index.html");
            }
            $results['rendered'][] = 'index.html';
            
            // 2. Render Public Stories list -> /stories/index.html
            $storiesDir = __DIR__ . '/stories';
            if (!file_exists($storiesDir)) {
                mkdir($storiesDir, 0755, true);
            }
            $storiesHtml = self::renderTemplate(__DIR__ . '/public-stories.php');
            if (file_put_contents($storiesDir . '/index.html', $storiesHtml) === false) {
                throw new Exception("Failed to write static stories catalog /stories/index.html");
            }
            $results['rendered'][] = 'stories/index.html';
            
            // 3. Render all published stories and pages -> /{slug}/index.html
            $stmt = $db->query("SELECT slug FROM posts WHERE status = 'published'");
            $publishedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $activeSlugs = [];
            foreach ($publishedPosts as $post) {
                $slug = $post['slug'];
                if (empty($slug)) {
                    continue;
                }
                
                $activeSlugs[] = $slug;
                $postDir = __DIR__ . '/' . $slug;
                
                if (!file_exists($postDir)) {
                    if (!mkdir($postDir, 0755, true)) {
                        $results['errors'][] = "Failed to create directory for slug: $slug";
                        continue;
                    }
                }
                
                $postHtml = self::renderTemplate(__DIR__ . '/public-home.php', $slug);
                if (file_put_contents($postDir . '/index.html', $postHtml) === false) {
                    $results['errors'][] = "Failed to write static index for slug: $slug";
                } else {
                    $results['rendered'][] = "$slug/index.html";
                }
            }
            
            // 4. Clean up any old static directories that are no longer active published slugs
            $ignoredDirs = ['admin', 'vendor', 'uploads', 'stories', '.git'];
            $items = scandir(__DIR__);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $dirPath = __DIR__ . '/' . $item;
                if (is_dir($dirPath)) {
                    // Ignore predefined system folders
                    if (in_array($item, $ignoredDirs)) {
                        continue;
                    }
                    // Ignore dot folders
                    if (strpos($item, '.') === 0) {
                        continue;
                    }
                    // If directory is not an active published slug, remove it
                    if (!in_array($item, $activeSlugs)) {
                        if (self::deleteDirectory($dirPath)) {
                            $results['cleaned'][] = $item;
                        } else {
                            $results['errors'][] = "Failed to clean up directory: $item";
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Helper to render a PHP template using output buffering.
     */
    private static function renderTemplate($templatePath, $slug = null) {
        $oldGet = $_GET;
        if ($slug !== null) {
            $_GET['slug'] = $slug;
        } else {
            unset($_GET['slug']);
        }
        
        ob_start();
        try {
            // Self-invoking closure to prevent variable collision with generator scope
            (static function($__template_file_path__) {
                require $__template_file_path__;
            })($templatePath);
        } catch (Exception $e) {
            ob_end_clean();
            $_GET = $oldGet;
            throw $e;
        }
        
        $output = ob_get_clean();
        $_GET = $oldGet;
        
        return $output;
    }
    
    /**
     * Recursively delete a directory and all of its contents.
     */
    private static function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}
