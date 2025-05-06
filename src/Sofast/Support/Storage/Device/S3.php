<?php
namespace Sofast\Support\Storage\Device;
use Sofast\Support\Storage\Device;
use Sofast\Core\Exception;

class S3 extends Device
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_TRACE = 'TRACE';

    /**
     * AWS区域常数
     */
	const S3_REGION = 'minio';

    /**
     * AWS ACL标志常量
     */
    const ACL_PRIVATE = 'private';
    const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    const ACL_AUTHENTICATED_READ = 'authenticated-read';

    /**
     * @var string
     */
    protected $accessKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var string
     */
    protected $bucket;
    
    /**
     * @var string
     */
    protected $region;
    
    /**
     * @var string
     */
    protected $acl = self::ACL_PRIVATE;
    
    /**
     * @var string
     */
    protected $host = '';
	
	/**
     * @var string
     */
    protected $root = '/';
    
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $amzHeaders;

    /**
     * S3 构建器
     *
     * @param string $host 主机地址
     * @param string $accessKey 用户
     * @param string $secretKey 密钥
     * @param string $bucket 桶名
     * @param string $region 位置
     * @param string $acl acl类型
     */
    public function __construct(string $host, string $accessKey, string $secretKey, string $bucket, string $region = self::S3_REGION, string $acl = self::ACL_PRIVATE)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket;
        $this->region = $region;
        $this->host = $host;
        $this->acl = $acl;
        $this->headers['host'] = $host;
        $this->amzHeaders = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'S3存储器';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '兼容AWS S3协议的桶存储';
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @param string $filename 文件名
     *
     * @return string
     */
    public function getPath(string $filename): string
    {
        $path = date("Y") . '/' . date("m");
        return $this->getRoot() . '/' . $path . '/' . $filename;
    }

    /**
     * 上传文件
     *
     * 上传一个选择的文件到存储桶
     *
     * @param string $source 源文件
     * @param string $path 目标路径
     *
     * @throws \Exception

     *
     * @return bool
     */
    public function upload($source, $path): bool
    {
        return $this->write($path, file_get_contents($source), mime_content_type($source));
    }

    /**
     * 读取指定的文件
     *
     * @param string $path 文件路径
     * 
     * @throws \Exception
     *
     * @return string
     */
    public function read(string $path): string
    {
        $uri = ($path !== '') ? '/' . $this->bucket . '/' . str_replace('%2F', '/', \rawurlencode($path)) : '/'. $this->bucket;
		$response = $this->call(self::METHOD_GET, $uri);
        return $response->body;
    }

    /**
     * 写入文件到指定的路径
     *
     * @param string $path 文件路径
     * @param string $data 文件数据
     * @throws \Exception
     * 
     * @return bool
     */
    public function write(string $path, string $data, string $contentType = ''): bool
    {
        $uri = $path !== '' ? '/' . $this->bucket . '/' . str_replace('%2F', '/', \rawurlencode($path)) : '/' . $this->bucket . '/';
        
        $this->headers['date'] = gmdate('D, d M Y H:i:s T');
        $this->headers['content-type'] = $contentType;
        $this->headers['content-md5'] = base64_encode(md5($data, true));
        $this->amzHeaders['x-amz-content-sha256'] = hash('sha256', $data);
        $this->amzHeaders['x-amz-acl'] = $this->acl;
        $this->call(self::METHOD_PUT, $uri, $data);
        return true;
    }

    /**
     * 将文件从给定源移动到给定路径，成功时返回true，失败时返回false。
     *
     * @param string $source 源路径
     * @param string $target 目标路径
     *
     * @return bool
     */
    public function move(string $source, string $target): bool
    {
        $type = $this->getFileMimeType($source);

        if ($this->write($target, $this->read($source), $type)) {
            $this->delete($source);
        }

        return true;
    }

    /**
     * 删除指定的文件，成功返回true，失败返回false。
     *
     * @param string $path 需要伤处的文件
     *
     * @return bool
     */
    public function delete(string $path, bool $recursive = false): bool
    {
        $uri = ($path !== '') ? '/' . $this->bucket . '/' . str_replace('%2F', '/', rawurlencode($path)) : '/' . $this->bucket . '/';
        $this->call(self::METHOD_DELETE, $uri);
        return true;
    }

    /**
     * 检查文件是否存在
     *
     * @param string $path 文件路径
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        try {
            $this->getInfo($path);
        } catch (Exception $th) {
            return false;
        }

        return true;
    }

    /**
     * 返回指定文件的大小
     *
     * @param $path 文件路径
     *
     * @return int
     */
    public function getFileSize(string $path): int
    {
        $response = $this->getInfo($path);
        return (int)($response['content-length'] ?? 0);
    }

    /**
     * 返回文件的mine类型。
     *
     * @param $path 文件路径
     *
     * @return string
     */
    public function getFileMimeType(string $path): string
    {
        $response = $this->getInfo($path);
        return $response['content-type'] ?? '';
    }

    /**
     * 返回文件的MD5值
     *
     * @param $path 文件路径
     *
     * @return string
     */
    public function getFileHash(string $path): string
    {
        $etag = $this->getInfo($path)['etag'] ?? '';
        return  (!empty($etag)) ? substr($etag, 1, -1) : $etag;
    }

    /**
     * 获取目录的比特大小，错误返回-1。
     *
     * @param $path 文件路径
     *
     * @return int
     */
    public function getDirectorySize(string $path): int
    {
        return -1;
    }

    /**
     * 获取存储剩余空间，不支持
     *
     * @return float
     */
    public function getPartitionFreeSpace(): float
    {
        return -1;
    }

    /**
     * 获取存储总空间，不支持
     *
     * @return float
     */
    public function getPartitionTotalSpace(): float
    {
        return -1;
    }

    /**
     * 获取文件信息
	 *
     * @return array
     */
    private function getInfo(string $path): array
    {
        $uri = $path !== '' ? '/' . $this->bucket .'/' . str_replace('%2F', '/', rawurlencode($path)) : '/' . $this->bucket . '/';
		$response = $this->call(self::METHOD_HEAD, $uri);
        return $response->headers;
    }

    /**
     * 生成 AWS V4 签名
     * @param string $method
     * @param string $uri
     * @return string
     */
    private function getSignatureV4(string $method, string $uri): string
    {
        $service = 's3';
        $region = $this->region;

        $algorithm = 'AWS4-HMAC-SHA256';
        $combinedHeaders = [];

        $amzDateStamp = substr($this->amzHeaders['x-amz-date'], 0, 8);

        //标准头文件
        foreach ($this->headers as $k => $v) {
            $combinedHeaders[strtolower($k)] = trim($v);
        }

        foreach ($this->amzHeaders as $k => $v) {
            $combinedHeaders[strtolower($k)] = trim($v);
        }

        uksort($combinedHeaders, [ & $this, 'sortMetaHeadersCmp']);

        //将NULL转化为字符并排序
        $parameters = [];
        uksort($parameters, [ & $this, 'sortMetaHeadersCmp']);
        $queryString = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
		
        // Payload
        $amzPayload = [$method];

        $qsPos = strpos($uri, '?');
        $amzPayload[] = ($qsPos === false ? $uri : substr($uri, 0, $qsPos));

        $amzPayload[] = $queryString;
        //将标准头注入到请求头中
        foreach ($combinedHeaders as $k => $v) {
            $amzPayload[] = $k . ':' . $v;
        }

        $amzPayload[] = '';
        $amzPayload[] = implode(';', array_keys($combinedHeaders));
        $amzPayload[] = $this->amzHeaders['x-amz-content-sha256'];
        
        $amzPayloadStr = implode("\n", $amzPayload);

        //验证作用域
        $credentialScope = [$amzDateStamp, $region, $service, 'aws4_request'];

        //要签名的字符串
        $stringToSignStr = implode("\n", [$algorithm, $this->amzHeaders['x-amz-date'],
            implode('/', $credentialScope), hash('sha256', $amzPayloadStr)]);

        //签名
        $kSecret = 'AWS4' . $this->secretKey;
        $kDate = hash_hmac('sha256', $amzDateStamp, $kSecret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSignStr, $kSigning);
		
        return $algorithm . ' ' . implode(',', [
            'Credential=' . $this->accessKey . '/' . implode('/', $credentialScope),
            'SignedHeaders=' . implode(';', array_keys($combinedHeaders)),
            'Signature=' . $signature,
        ]);
    }

    /**
     * 获取S3回应信息
     *
     * @return object
     */
    private function call(string $method, string $uri, string $data = '')
    {
        $url = 'http://' . $this->headers['host'] . $uri;
        $response = new \stdClass;
        $response->body = '';
        $response->headers = [];

        $curl = curl_init();
        //curl_setopt($curl, CURLOPT_USERAGENT, 'sf/storage');
        curl_setopt($curl, CURLOPT_URL, $url);

        //头信息
        $httpHeaders = [];
        $this->amzHeaders['x-amz-date'] = gmdate('Ymd\THis\Z');

        if (!isset($this->amzHeaders['x-amz-content-sha256'])) {
            $this->amzHeaders['x-amz-content-sha256'] = hash('sha256', $data);
        }
		
        foreach ($this->amzHeaders as $header => $value) {
            if (strlen($value) > 0) {
                $httpHeaders[] = $header . ': ' . $value;
            }
        }

        foreach ($this->headers as $header => $value) {
            if (strlen($value) > 0) {
                $httpHeaders[] = $header . ': ' . $value;
            }
        }

        $httpHeaders[] = 'Authorization: ' . $this->getSignatureV4($method, $uri);
		
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, function ($curl, string $data) use ($response) {
            $response->body .= $data;
            return strlen($data);
        });
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, string $header) use (&$response) {
            $len = strlen($header);
            $header = explode(':', $header, 2);

            if (count($header) < 2) { //忽略无效头信息
                return $len;
            }

            $response->headers[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        });
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        
        switch ($method) {
            case self::METHOD_PUT:
            case self::METHOD_POST:
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case self::METHOD_HEAD:
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
        }

        $result = curl_exec($curl);
        
        if (!$result) {
            throw new Exception(curl_error($curl));
        }
        $response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response->code >= 400) {
            throw new Exception($response->body, $response->code);
        }

        curl_close($curl);
        
        //解析XML
        if (isset($response->headers['content-type']) && $response->headers['content-type'] == 'application/xml') {
            $response->body = simplexml_load_string($response->body);
        }
		
        return $response;
    }

    /**
     * 元标题的排序比较
     *
     * @internal 用于对x-amz元标题进行排序
     * @param string $a String A
     * @param string $b String B
     * @return integer
     */
    private function sortMetaHeadersCmp($a, $b)
    {
        $lenA = strlen($a);
        $lenB = strlen($b);
        $minLen = min($lenA, $lenB);
        $ncmp = strncmp($a, $b, $minLen);
        if ($lenA == $lenB) {
            return $ncmp;
        }

        if (0 == $ncmp) {
            return $lenA < $lenB ? -1 : 1;
        }

        return $ncmp;
    }
}