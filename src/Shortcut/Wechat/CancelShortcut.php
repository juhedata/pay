<?php

declare(strict_types=1);

namespace Yansongda\Pay\Shortcut\Wechat;

use Yansongda\Artful\Contract\ShortcutInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Plugin\AddPayloadBodyPlugin;
use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Artful\Plugin\StartPlugin;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Plugin\Wechat\AddRadarPlugin;
use Yansongda\Pay\Plugin\Wechat\NormalizeParamsPlugin;
use Yansongda\Pay\Plugin\Wechat\ResponsePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\AddPayloadSignaturePlugin as V2AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\Pay\Pos\CancelPlugin as V2CancelPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin as V2VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\CancelPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\is_wechat_v2;

class CancelShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        if (is_wechat_v2(get_provider_config('wechat', $params))) {
            return $this->v2Plugins();
        }

        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "您所提供的 action 方法 [{$method}] 不支持，请参考文档或源码确认");
    }

    protected function v2Plugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            V2CancelPlugin::class,
            V2AddPayloadSignaturePlugin::class,
            AddPayloadBodyPlugin::class,
            AddRadarPlugin::class,
            V2VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function defaultPlugins(): array
    {
        return $this->mchTransferPlugins();
    }

    protected function mchTransferPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            CancelPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
