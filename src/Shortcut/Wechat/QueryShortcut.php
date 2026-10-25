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
use Yansongda\Pay\Plugin\Wechat\V2\Pay\QueryPlugin as V2QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V2\VerifySignaturePlugin as V2VerifySignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\AddPayloadSignaturePlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\QueryByWxPlugin as MchTransferQueryByWxPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\QueryPlugin as MchTransferQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Detail\QueryPlugin as TransferQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryPlugin as AppQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryRefundPlugin as AppQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryPlugin as CombineQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryRefundPlugin as CombineQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryPlugin as H5QueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryRefundPlugin as H5QueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryPlugin as JsapiQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryRefundPlugin as JsapiQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryPlugin as MiniQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryRefundPlugin as MiniQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryPlugin as NativeQueryPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryRefundPlugin as NativeQueryRefundPlugin;
use Yansongda\Pay\Plugin\Wechat\V3\VerifySignaturePlugin;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\is_wechat_v2;

class QueryShortcut implements ShortcutInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function getPlugins(array $params): array
    {
        $isV2 = is_wechat_v2(get_provider_config('wechat', $params));

        if ($isV2) {
            return $this->v2Plugins();
        }

        if (isset($params['combine_out_trade_no'])) {
            return $this->combinePlugins();
        }

        $method = Str::camel($params['_action'] ?? 'default').'Plugins';

        if (method_exists($this, $method)) {
            return $this->{$method}($params);
        }

        throw new InvalidParamsException(Exception::PARAMS_SHORTCUT_ACTION_INVALID, "您所提供的 action 方法 [{$method}] 不支持，请参考文档或源码确认");
    }

    protected function v2Plugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            V2QueryPlugin::class,
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
            AppQueryPlugin::class,
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
            CombineQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function h5Plugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            H5QueryPlugin::class,
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
            JsapiQueryPlugin::class,
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
            MiniQueryPlugin::class,
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
            NativeQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function mchTransferPlugins(array $params): array
    {
        $query = MchTransferQueryPlugin::class;

        if (isset($params['transfer_bill_no'])) {
            $query = MchTransferQueryByWxPlugin::class;
        }

        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            $query,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundPlugins(): array
    {
        return $this->refundJsapiPlugins();
    }

    protected function refundAppPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            AppQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundCombinePlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            CombineQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundH5Plugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            H5QueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundJsapiPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            JsapiQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundMiniPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            MiniQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function refundNativePlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            NativeQueryRefundPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }

    protected function transferPlugins(): array
    {
        return [
            StartPlugin::class,
            NormalizeParamsPlugin::class,
            TransferQueryPlugin::class,
            AddPayloadBodyPlugin::class,
            AddPayloadSignaturePlugin::class,
            AddRadarPlugin::class,
            VerifySignaturePlugin::class,
            ResponsePlugin::class,
            ParserPlugin::class,
        ];
    }
}
