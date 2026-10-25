<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V2;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_wechat_sign_v2;

/**
 * 微信支付 V2 回调插件：解析 XML 并用 MD5 验签.
 */
class CallbackPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V2][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $request = $rocket->getParams()['_request'] ?? null;
        $params = $rocket->getParams()['_params'] ?? [];

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 微信回调参数不正确');
        }

        $body = (string) $request->getBody();
        $data = Arr::wrapXml($body);

        if (empty($data)) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 微信回调 XML 解析失败');
        }

        $config = get_provider_config('wechat', $params);

        verify_wechat_sign_v2($config, $data);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination(new Collection($data))
            ->setDestinationOrigin($request)
            ->setPayload($params);

        Logger::info('[Wechat][V2][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
