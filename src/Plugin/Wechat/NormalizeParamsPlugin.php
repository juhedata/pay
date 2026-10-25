<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * 参数字段名规范化插件.
 *
 * 在 StartPlugin 之后、业务 PayPlugin 之前执行，统一处理跨支付渠道的字段名差异：
 * - `_notify_url` → `notify_url`（兼容支付宝风格字段名）
 */
class NormalizeParamsPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][NormalizeParamsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (null !== $payload) {
            // 兼容 _notify_url（支付宝风格）→ notify_url（微信标准）
            if ($payload->has('_notify_url') && !$payload->has('notify_url')) {
                $payload->set('notify_url', $payload->get('_notify_url'));
            }
        }

        Logger::info('[Wechat][NormalizeParamsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
