<?php //CODE BY HW
namespace KKdayB2BApi;
use Hwphp\Curl;

class KKdayB2BApi
{
    /**
     * 正式服接口地址
     * @var string
     */
    public $Host = 'https://api-b2d.kkday.com/v3';

    /**
     * 测试服接口地址
     * @var string
     */
    public $TestHost = 'https://api-b2d.sit.kkday.com/v3';

    /**
     * 是否启用测试环境
     * @var bool
     */
    public $TestOn;

    /**
     * 身份标识UUID
     * @var string
     */
    public $Uuid;

    /**
     * 身份标识Token
     * @var string
     */
    public $Token;

    /**
     * 接口凭证 API KEY
     * @var string
     */
    public $ApiKey;

    /**
     * 默认语系
     * @var string
     */
    public $Locale;

    /**
     * 日志目录
     * @var string
     */
    public $LogDir;

    /**
     * 是否开启日志
     * @var string
     */
    public $LogOn = true;

    /**
     * 构造方法
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 请求接口
     * @param string $path
     * @param array $data
     * @param string $method
     * @param array $query
     * @param bool $authKey
     * @return false|mixed
     */
    public function Request(string $path, array $data, string $method = 'POST', array $query = [], bool $authKey = true)
    {
        try {
            $curl = new Curl();
            $url = $this->getHost() . '/' . trim($path, '/');

            if (!empty($query)) {
                $url = $url . '?' . http_build_query($query);
            }

            $curl->setUrl($url);
            $method = strtoupper($method);

            if ('POST' === $method) {
                $curl->setPost();
            }

            if (!empty($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                $curl->setData($data);
            }

            if ($authKey) {
                $curl->addHeader('Authorization: Bearer ' . $this->getApiKey());
            }

            $curl->setNoSSL();
            $curl->addHeader('Content-type: application/json');
            $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
            $curl->setOption(CURLOPT_TIMEOUT, 30);

            $content = $curl->exec();

            $this->Log('DEBUG', [
                'time' => date('Y-m-d H:i:s'),
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'response' => $content,
            ]);

            if (empty($content)) {
                return false;
            }

            $data = json_decode($content, true);

            if (empty($data)) {
                return false;
            }

            return $data;

        } catch(\Exception $e) {
            $this->Log('ERROR', [
                'time' => date('Y-m-d H:i:s'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'args' => func_get_args(),
            ]);
        }

        return false;
    }

    /**
     * 获取接口地址
     * @return string
     */
    public function getHost(): string
    {
        return $this->TestOn ? $this->TestHost : $this->Host;
    }

    /**
     * 获取接口请求凭证
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->ApiKey;
    }

    /**
     * 记录日志
     * @param $level
     * @param $data
     * @return void
     */
    public function Log($level, $data)
    {
        if (!$this->LogOn || empty($this->LogDir)) {
            return;
        }

        if (!is_dir($this->LogDir) && !mkdir($this->LogDir, 0755, true)) {
            return;
        }

        $file = rtrim($this->LogDir, '/') . '/' . date('Ymd') . '.log';

        if ($level === 'ERROR') {
            $file = rtrim($this->LogDir, '/') . '/' . date('Ymd') . '_err.log';
        }

        @file_put_contents($file, print_r($data, true) . PHP_EOL, FILE_APPEND);
    }

    /**
     * 查询商品列表
     * @param array $data
     * @return false|mixed
     */
    public function Search(array $data = [])
    {
        return $this->Request('Search', array_merge([
            'locale' => $this->Locale,
        ], $data));
    }

    /**
     * 单一商品完整资料
     * @param array $data
     * @return false|mixed
     */
    public function ProductQueryProduct(array $data = [])
    {
        return $this->Request('Product/QueryProduct', array_merge([
            'locale' => $this->Locale,
        ], $data));
    }

    /**
     * 套餐
     * @param array $data
     * @return false|mixed
     */
    public function ProductQueryPackage(array $data = [])
    {
        return $this->Request('Product/QueryPackage', array_merge([
            'locale' => $this->Locale,
        ], $data));
    }

    /**
     * 候补场次
     * @param array $data
     * @return false|mixed
     */
    public function ProductQueryBackupEvent(array $data = [])
    {
        return $this->Request('Product/QueryBackupEvent', $data);
    }

    /**
     * 评论
     * @param array $data
     * @return false|mixed
     */
    public function ProductQueryReview(array $data = [])
    {
        return $this->Request('Product/QueryReview', array_merge([
            'locale' => $this->Locale,
        ], $data));
    }

    /**
     * 市场列表
     * @return false|mixed
     */
    public function ProductQueryState()
    {
        return $this->Request('Product/QueryState', [], 'GET');
    }

    /**
     * 国家代码列表
     * @param array $query
     * @return false|mixed
     */
    public function ProductQueryCountryCode(array $query = [])
    {
        return $this->Request('Product/QueryCountryCode', [], 'GET', array_merge([
            'locale' => $this->Locale,
        ], $query));
    }

    /**
     * 城市列表
     * @param array $query
     * @return false|mixed
     */
    public function ProductQueryCityList(array $query = [])
    {
        return $this->Request('Product/QueryCityList', [], 'GET', array_merge([
            'locale' => $this->Locale,
        ], $query));
    }

    /**
     * 查询剩余额度
     * @param array $data
     * @return false|mixed
     */
    public function BookingQueryAmount(array $data = [])
    {
        return $this->Request('Booking/QueryAmount', $data);
    }

    /**
     * 创建订单
     * @param array $data
     * @return false|mixed
     */
    public function Booking(array $data = [])
    {
        return $this->Request('Booking', $data);
    }

    /**
     * 取消订单
     * @param array $data
     * @return false|mixed
     */
    public function OrderCancel(array $data = [])
    {
        return $this->Request('Order/Cancel', $data);
    }

    /**
     * 订单列表
     * @param array $data
     * @return false|mixed
     */
    public function OrderQueryOrders(array $data = [])
    {
        return $this->Request('Order/QueryOrders', $data);
    }

    /**
     * 订单详情
     * @param string $orderNo
     * @return false|mixed
     */
    public function OrderQueryOrderDtl(string $orderNo)
    {
        return $this->Request('Order/QueryOrderDtl/' . $orderNo, [], 'GET');
    }

    /**
     * 订单商品细节
     * @param string $orderNo
     * @return false|mixed
     */
    public function OrderQueryOrderDtlInfo(string $orderNo)
    {
        return $this->Request('Order/QueryOrderDtlInfo/' . $orderNo, [], 'GET');
    }

    /**
     * 凭证清单
     * @param array $data
     * @return false|mixed
     */
    public function VoucherQueryVoucherList(array $data = [])
    {
        return $this->Request('Voucher/QueryVoucherList', $data);
    }

    /**
     * 凭证下载
     * @param array $data
     * @return false|mixed
     */
    public function VoucherDownload(array $data = [])
    {
        return $this->Request('Voucher/download', $data);
    }
}
