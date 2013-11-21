<?php

namespace PeerJ\ArticleMetrics;

/**
 * Base class for article metrics, to be extended for specific sources
 */
abstract class Metrics
{
    /** @var resource */
    protected $curl;

    /** @var array */
    protected $headers;

    /** @var string */
    protected $name;

    /** @var string */
    protected $suffix = 'json';

    /** @var string */
    protected $dir;

    /** @var array */
    protected $config = array();

    /**
     * Fetch metrics for a single article
     *
     * @param array $article
     *
     * @throws \Exception
     */
    abstract public function fetch($article);

    /**
     * Parse the output for all articles
     *
     * @return void
     */
    abstract public function parse();

    /**
     * Create a cURL instance
     * Set configuration
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->curl = curl_init();

        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_VERBOSE => true,
                CURLOPT_HEADERFUNCTION => array($this, 'header'),
                CURLOPT_ENCODING => 'gzip,deflate',
            )
        );
    }

    /**
     * Fetch metrics for all articles
     *
     * @throws \Exception
     */
    public function fetchAll()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Make sure the output directory for data exists
     *
     * @param string $dir
     */
    public function setDataDir($dir)
    {
        if (!isset($this->name)) {
            exit(sprintf("The 'name' property must be set for class %s\n", get_class()));
        }

        $this->dir = rtrim($dir, '/') . '/' . $this->name;

        $sourcedir = $this->dir . '/original';

        if (!file_exists($sourcedir)) {
            mkdir($sourcedir, 0777, true);
        }
    }

    /**
     * Build the path to the output directory for data
     *
     * @param array $article
     *
     * @return string
     */
    protected function getDataFile($article)
    {
        return $this->dir . sprintf('/original/%d.%s', $article['id'], $this->suffix);
    }

    /**
     * Open the output CSV file for writing
     *
     * @return resource
     */
    protected function getOutputFile()
    {
        return fopen($this->dir . '/' . $this->name . '.csv', 'w');
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function idFromFile($file)
    {
        return basename($file, '.' . $this->suffix);
    }

    /**
     * Find all the data files for this metric
     *
     * @return array
     */
    protected function files()
    {
        $files = glob($this->dir . '/original/*.' . $this->suffix);
        sort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Delete all the data files for this metric
     */
    public function clean()
    {
        array_map('unlink', $this->files());
    }

    /**
     * Fetch a URL and write the contents to a file
     *
     * @param string $url
     * @param array  $params
     * @param string $file
     * @param int    $tries
     *
     * @return bool
     * @throws \Exception
     */
    public function get($url, array $params, $file, $tries = 0)
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        print "$url\n";

        $this->headers = array();

        $output = fopen($file, 'w');

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_FILE, $output);

        $result = curl_exec($this->curl);
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        fclose($output);

        switch ($code) {
            case 200:
                // nothing to return, as writing to file
                break;

            case 429: // rate limit
                if ($tries == 5) {
                    throw new \Exception('Rate limited too many times');
                }

                $this->delay();
                $this->get($url, array(), $file, ++$tries);
                break;

            default:
                // TODO: unlink file?
                $message = sprintf('Response not OK: %d %s', $code, $result);

                throw new \Exception($message);
        }
    }

    /**
     * Store response headers in an array
     *
     * @param resource $curl
     * @param string   $header
     *
     * @return int header length
     */
    protected function header(
        /** @noinspection PhpUnusedParameterInspection */
        $curl, $header)
    {
        $parts = preg_split('/:\s+/', $header, 2);

        if (isset($parts[1])) {
            list($name, $value) = $parts;
            $this->headers[strtolower($name)] = $value;
        }

        return strlen($header);
    }

    /**
     * Delay if rate limit is reached
     */
    protected function delay()
    {
        if (!isset($this->headers['x-rate-limit-reset'])) {
            exit('Rate limited, but no rate limit header found');
        }

        $delay = $this->headers['x-rate-limit-reset'] - time();

        if ($delay < 10) {
            $delay = 60 * 15; // 15 minute delay if the given delay seems unreasonably small (can be due to server time differences)
        }

        printf('Sleeping for %d seconds', $delay);
        sleep($delay);
    }
}
