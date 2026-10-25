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
use Yansongda\Pay\Plugin\Wechat\V2\Pay\ClosePlugin as V2ClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin as V2VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\ClosePlugin as AppClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\ClosePlugin as CombineClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\ClosePlugin as H5ClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\ClosePlugin as JsapiClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\ClosePlugin as MiniClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\ClosePlugin as NativeClosePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\is_wechat_v2;

class CloseShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        if (is_wechat_v2(get_provider_config('wechat', $params))) {
            return $this->v2Plugins();
        }

        if (isset($params['combine_out_trade_no']) || isset($params['sub_orders'])) {
            return $this->combinePlugins();
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
            V2ClosePlugin::class,
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
        return $this->jsapiPlugins();
    }

    protected function appPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            AppClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function H5Plugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            H5ClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function jsapiPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            JsapiClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function miniPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            MiniClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function nativePlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            NativeClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function combinePlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            CombineClosePlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
