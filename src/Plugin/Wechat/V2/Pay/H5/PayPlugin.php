<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Pay\H5;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Packer\XmlPacker;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=15_1
 */
class PayPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws Throwable                随机字符串生成失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][Pay][H5][PayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $params = $rocket->getParams();
        $config = get_provider_config('wechat', $params);

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: H5 下单，参数为空');
        }

        $data = Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)
            ? $this->service($payload, $params, $config)
            : [];

        $rocket->setPacker(XmlPacker::class)
            ->mergePayload(array_merge(
                [
                    '_url' => 'pay/unifiedorder',
                    '_content_type' => 'application/xml',
                    'appid' => $config[get_wechat_type_key($params)] ?? '',
                    'mch_id' => $config['mch_id'] ?? '',
                    'nonce_str' => Str::random(32),
                    'sign_type' => 'MD5',
                    'trade_type' => 'MWEB',
                    'notify_url' => $payload->get('notify_url', $config['notify_url'] ?? ''),
                ],
                $data
            ));

        Logger::info('[Wechat][V2][Pay][H5][PayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function service(Collection $payload, array $params, array $config): array
    {
        return [
            'sub_appid' => $config['sub_'.get_wechat_type_key($params)] ?? '',
            'sub_mch_id' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
        ];
    }
}
