<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $count = 0;

    public function increment(): void
    {
        $this->count = $this->count + 1;
    }
}; ?>

<div>
    <p id="result"></p>
    <button id="start-scanning">Start Scanning</button>
    <script type="module">
        import "https://cdn.jsdelivr.net/npm/scanbot-web-sdk@7.0.0/bundle/ScanbotSDK.ui2.min.js";

        const resultBox = document.getElementById("result");

        const wasm_path = "https://cdn.jsdelivr.net/npm/scanbot-web-sdk@7.0.0/bundle/bin";
        const sdk = await ScanbotSDK.initialize({ enginePath: `${wasm_path}/barcode-scanner/` });

        document.getElementById("start-scanning").onclick = async () => {
            const config = new ScanbotSDK.UI.Config.BarcodeScannerScreenConfiguration();
            config.scannerConfiguration.barcodeFormats = ["PDF_417"];
            config.viewFinder.aspectRatio.height = 1;
            config.viewFinder.aspectRatio.width = 3;

            const scanResult = await ScanbotSDK.UI.createBarcodeScanner(config);
            if (scanResult?.items === undefined || scanResult.items.length == 0) {
                resultBox.innerText = "Scanning aborted by the user";
                return;
            }

            const barcode = scanResult?.items[0].barcode;
            const extractedDocument = barcode.extractedDocument;

            const extractDocumentFields = (document) => {
                const fields = [];

                const processFields = (input) => {
                    if (!Array.isArray(input))
                        return;

                    input.forEach((field) => {
                        if (field.type?.name && field.value?.text) {
                            fields.push(field);
                        }
                    });
                };

                processFields(document.fields);

                if (Array.isArray(document.children)) {
                    document.children.forEach((child) => {
                        processFields(child.fields);
                    });
                }

                return fields;
            }

            const documentResult = extractDocumentFields(extractedDocument);
            resultBox.innerText = documentResult.map((field) => `${field.type.name}: ${field.value.text}`).join("\n");
        };
    </script>
   
</div>
