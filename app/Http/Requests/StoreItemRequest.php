<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreItemRequest
 * 
 * Validates all input for create and update operations.
 * Includes cross-field validation and custom error messages.
 */
class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Includes unique SKU constraint that ignores the current item during updates.
     * 
     * @return array
     */
    public function rules(): array
    {
        // Get the current item for unique SKU validation during updates
        // Using safe null coalescing operator (?->) for cleaner syntax
        $stockItem = $this->route('stock_item');
        $itemId = $stockItem?->id;

        return [
            // Item name: Required, text-only, 3-255 characters
            // Prevents empty/too-short/too-long product names
            'item_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            // SKU: Unique stock keeping unit identifier
            // Format: Uppercase letters [A-Z], numbers [0-9], hyphens [-]
            // Examples: TOOL-001, PDR-002, WRN-BLUE-10
            // Ignores current item ID during updates (allows same item to be edited)
            'sku' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/', // Pattern: Only [A-Z0-9-] characters
                Rule::unique('stock_items', 'sku')->ignore($itemId), // Allow re-submission without unique violation
            ],

            // Category: One of predefined warehouse categories
            // Uses constant from StockItem model to avoid hardcoding
            // Ensures data consistency across application
            'category' => [
                'required',
                'string',
                'max:100',
                Rule::in(\App\Models\StockItem::CATEGORIES), // Uses model constant instead of duplicated array
            ],

            // Quantity: Non-negative integer count
            // Min 0 (out of stock is valid state)
            // Max 999,999 (prevents decimal storage)
            'quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],

            // Unit Price: Positive decimal currency amount
            // Range: $0.01 to $99,999.99
            // Regex ensures proper decimal format (e.g., 19.99 not 19.999)
            'unit_price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Validates: 10 or 10.99 format only
            ],
        ];
    }

    /**
     * Get custom error messages for validation failures.
     * Provides clear, user-friendly feedback.
     * 
     * @return array
     */
    public function messages(): array
    {
        return [
            // Item name messages
            'item_name.required' => '🔴 Item name is required. Please provide a descriptive name.',
            'item_name.min' => '🔴 Item name must be at least 3 characters long.',
            'item_name.max' => '🔴 Item name cannot exceed 255 characters.',
            'item_name.string' => '🔴 Item name must be text only.',

            // SKU messages
            'sku.required' => '🔴 SKU (Stock Keeping Unit) is required.',
            'sku.max' => '🔴 SKU cannot exceed 50 characters.',
            'sku.unique' => '🔴 This SKU already exists in the system. Each item must have a unique SKU.',
            'sku.regex' => '🔴 SKU must contain only uppercase letters (A-Z), numbers (0-9), and hyphens (-). Example: TOOL-001',
            'sku.string' => '🔴 SKU must be text.',

            // Category messages
            'category.required' => '🔴 Category is required. Please select from the dropdown.',
            'category.in' => '🔴 Selected category is invalid. Please choose: Tools, Hardware, Supplies, Equipment, or Safety.',
            'category.max' => '🔴 Category cannot exceed 100 characters.',

            // Quantity messages
            'quantity.required' => '🔴 Quantity is required.',
            'quantity.integer' => '🔴 Quantity must be a whole number (no decimals).',
            'quantity.min' => '🔴 Quantity cannot be negative. Minimum is 0.',
            'quantity.max' => '🔴 Quantity cannot exceed 999,999 units.',

            // Unit price messages
            'unit_price.required' => '🔴 Unit price is required.',
            'unit_price.numeric' => '🔴 Unit price must be a valid number.',
            'unit_price.min' => '🔴 Unit price must be greater than $0.00.',
            'unit_price.max' => '🔴 Unit price cannot exceed $99,999.99.',
            'unit_price.regex' => '🔴 Unit price must be a valid decimal format with up to 2 decimal places. Example: 19.99',
        ];
    }

    /**
     * Prepare the data for validation.
     * Clean and normalize input data.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert SKU to uppercase for consistency
        $this->merge([
            'sku' => strtoupper($this->input('sku', '')),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     * 
     * @return array
     */
    public function attributes(): array
    {
        return [
            'item_name' => 'Item Name',
            'sku' => 'SKU',
            'category' => 'Category',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
        ];
    }
}
