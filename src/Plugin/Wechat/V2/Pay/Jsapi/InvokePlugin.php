<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2\Pay\Jsapi;

use Closure;
use Throwable;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\get_wechat_sign_v2;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * 生成公众号 JSAPI 前端调起参数.
 *
 * @see https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
 */
class InvokePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws Exception
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
     * @throws Throwable                生成随机串失败
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Wechat][V2][Pay][Jsapi][InvokePlugin] 插件开始装载', ['rocket' => $rocket]);

        $destination = $rocket->getDestination();
        $prepayId = $destination?->get('prepay_id') ?? null;

        if (is_null($prepayId)) {
            $responseData = $destination?->all() ?? [];
            Logger::error('[Wechat][V2][Pay][Jsapi][InvokePlugin] 预下单失败：响应缺少 `prepay_id` 参数', $responseData);

            // 优先返回微信的错误信息
            $errorMessage = $destination?->get('err_code_des')
                ?? $destination?->get('return_msg')
                ?? '预下单失败：响应缺少 `prepay_id` 参数，请自行检查参数是否符合微信要求';

            throw new InvalidResponseException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, $errorMessage, $responseData);
        }

        $params = $rocket->getParams();
        $config = get_provider_config('wechat', $params);
        $payload = $rocket->getPayload();

        $rocket->setDestination($this->getInvokeConfig($payload, $params, $config, $prepayId));

        Logger::info('[Wechat][V2][Pay][Jsapi][InvokePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidConfigException
     * @throws Throwable              生成随机串失败
     */
    protected function getInvokeConfig(?Collection $payload, array $params, array $config, string $prepayId): Config
    {
        $invokeConfig = new Config([
            'appId' => $this->getAppId($payload, $params, $config),
            'timeStamp' => time().'',
            'nonceStr' => Str::random(32),
            'package' => 'prepay_id='.$prepayId,
            'signType' => 'MD5',
        ]);

        $invokeConfig->set('paySign', get_wechat_sign_v2($config, $invokeConfig->all()));

        return $invokeConfig;
    }

    protected function getAppId(?Collection $payload, array $params, array $config): string
    {
        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            return $payload?->get('_invoke_appid') ?? $config['sub_'.get_wechat_type_key($params)] ?? '';
        }

        return $payload?->get('_invoke_appid') ?? $config[get_wechat_type_key($params)] ?? '';
    }
}
