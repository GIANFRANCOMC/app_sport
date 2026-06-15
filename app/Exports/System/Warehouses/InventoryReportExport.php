<?php

declare(strict_types=1);

namespace App\Exports\System\Warehouses;

use App\Services\System\Warehouses\StockManagement\StockManagementService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    ShouldAutoSize,
    WithCustomValueBinder,
    WithEvents,
    WithHeadings,
    WithMapping,
    WithStyles
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Fill};
use PhpOffice\PhpSpreadsheet\Cell\{Cell, DataType, DefaultValueBinder};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class InventoryReportExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithStyles {

    private int $currentRow = 1;
    private array $attentionRows = [];

    public function __construct(
        private readonly int $companyId,
        private readonly string $view,
        private readonly array $filters
    ) {
    }

    public function collection(): Collection {

        if($this->view === "stock") {

            return StockManagementService::getStockReport(
                $this->companyId,
                (int) ($this->filters["warehouse_id"] ?? 0),
                (string) ($this->filters["product_search"] ?? "")
            );

        }

        $filters = $this->filters;

        if($this->view === "transfers") {

            $filters["origin_types"] = ["transfer_in", "transfer_out"];

        }

        return StockManagementService::getKardexReport($this->companyId, $filters);

    }

    public function headings(): array {

        if($this->view === "stock") {

            return [
                "Código interno",
                "Código de barras",
                "Producto",
                "Stock actual",
                "Stock mínimo",
                "Situación"
            ];

        }

        if($this->view === "valued") {

            return [
                "Fecha",
                "Almacén",
                "Código interno",
                "Código de barras",
                "Producto",
                "Movimiento",
                "Origen",
                "Variación",
                "Costo unitario",
                "Valor anterior",
                "Valor del movimiento",
                "Valor resultante",
                "Motivo",
                "Referencia",
                "Responsable"
            ];

        }

        return [
            "Fecha",
            "Almacén",
            "Código interno",
            "Código de barras",
            "Producto",
            "Movimiento",
            "Origen",
            "Saldo anterior",
            "Variación",
            "Saldo resultante",
            "Motivo",
            "Referencia",
            "Responsable"
        ];

    }

    public function map($record): array {

        $this->currentRow++;

        if($this->view === "stock") {

            $stock = (float) ($record->stock_quantity ?? 0);
            $minimum = (float) ($record->minimum_stock ?? 0);
            $status = $stock <= 0
                ? "Sin existencias"
                : ($stock <= $minimum ? "Stock bajo" : "Stock saludable");

            if($stock <= $minimum) {

                $this->attentionRows[] = $this->currentRow;

            }

            return [
                $record->internal_code,
                $record->barcode,
                $record->name,
                $stock,
                $minimum,
                $status
            ];

        }

        if($this->view === "valued") {

            return [
                optional($record->created_at)->format("d/m/Y H:i"),
                $record->warehouse?->name,
                $record->item?->internal_code,
                $record->item?->barcode,
                $record->item?->name,
                $this->movementLabel((string) $record->movement_type),
                $this->originLabel((string) $record->origin_type),
                (float) $record->quantity_change,
                (float) $record->unit_cost,
                (float) $record->value_before,
                (float) $record->value_change,
                (float) $record->value_after,
                $record->reason,
                $record->reference,
                $record->user?->name ?? "Proceso del sistema"
            ];

        }

        return [
            optional($record->created_at)->format("d/m/Y H:i"),
            $record->warehouse?->name,
            $record->item?->internal_code,
            $record->item?->barcode,
            $record->item?->name,
            $this->movementLabel((string) $record->movement_type),
            $this->originLabel((string) $record->origin_type),
            (float) $record->quantity_before,
            (float) $record->quantity_change,
            (float) $record->quantity_after,
            $record->reason,
            $record->reference,
            $record->user?->name ?? "Proceso del sistema"
        ];

    }

    public function styles(Worksheet $sheet): array {

        $lastColumn = match($this->view) {
            "stock" => "F",
            "valued" => "O",
            default => "M"
        };

        $sheet->freezePane("A2");
        $sheet->setAutoFilter("A1:{$lastColumn}1");
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            "font" => ["bold" => true, "color" => ["rgb" => "FFFFFF"]],
            "fill" => [
                "fillType" => Fill::FILL_SOLID,
                "startColor" => ["rgb" => "1A1A35"]
            ],
            "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER]
        ]);

        return [];

    }

    public function bindValue(Cell $cell, $value): bool {

        $textColumns = match($this->view) {
            "stock" => ["A", "B", "C", "F"],
            "valued" => ["A", "B", "C", "D", "E", "F", "G", "M", "N", "O"],
            default => ["A", "B", "C", "D", "E", "F", "G", "K", "L", "M"]
        };

        if($cell->getRow() > 1 && in_array($cell->getColumn(), $textColumns, true)) {

            $cell->setValueExplicit((string) ($value ?? ""), DataType::TYPE_STRING);

            return true;

        }

        return parent::bindValue($cell, $value);

    }

    public function registerEvents(): array {

        return [
            AfterSheet::class => function(AfterSheet $event) {

                foreach($this->attentionRows as $row) {

                    $event->sheet->getStyle("D{$row}:F{$row}")->applyFromArray([
                        "font" => ["color" => ["rgb" => "92400E"]],
                        "fill" => [
                            "fillType" => Fill::FILL_SOLID,
                            "startColor" => ["rgb" => "FEF3C7"]
                        ]
                    ]);

                }

            }
        ];

    }

    private function movementLabel(string $type): string {

        return [
            "entry" => "Entrada",
            "exit" => "Salida",
            "correction" => "Corrección"
        ][$type] ?? $type;

    }

    private function originLabel(string $origin): string {

        return [
            "product_opening" => "Creación de producto",
            "manual" => "Operación manual",
            "sale" => "Venta",
            "sale_cancellation" => "Devolución automática por anulación",
            "purchase" => "Compra",
            "purchase_cancellation" => "Anulación de compra",
            "transfer_out" => "Traslado enviado",
            "transfer_in" => "Traslado recibido",
            "replenishment" => "Reposición",
            "customer_return" => "Devolución de cliente",
            "supplier_return" => "Devolución a proveedor",
            "physical_count" => "Toma física"
        ][$origin] ?? $origin;

    }

}
