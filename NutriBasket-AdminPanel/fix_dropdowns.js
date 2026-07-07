// Fix for dropdown initialization issues
$(document).ready(function() {
    
    // Debug function to check if elements exist
    function debugElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            console.log(`✅ Element ${elementId} found`);
            return true;
        } else {
            console.log(`❌ Element ${elementId} not found`);
            return false;
        }
    }

    // Initialize store dropdown
    if (debugElement('store_id')) {
        $('#store_id').select2({
            ajax: {
                url: window.location.origin + '/admin/store/get-stores',
                data: function(params) {
                    return {
                        q: params.term || '', // search term
                        page: params.page || 1,
                        module_id: window.currentModuleId || null,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                error: function(xhr, status, error) {
                    console.error('Store dropdown error:', error);
                }
            },
            placeholder: 'Select a store',
            allowClear: true,
            minimumInputLength: 0
        });
    }

    // Initialize category dropdown
    if (debugElement('category_id')) {
        $('#category_id').select2({
            ajax: {
                url: window.location.origin + '/admin/item/get-categories',
                data: function(params) {
                    return {
                        q: params.term || '', // search term
                        page: params.page || 1,
                        module_id: window.currentModuleId || null,
                        parent_id: 0
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                error: function(xhr, status, error) {
                    console.error('Category dropdown error:', error);
                }
            },
            placeholder: 'Select a category',
            allowClear: true,
            minimumInputLength: 0
        });
    }

    // Initialize sub-category dropdown
    if (debugElement('sub-categories')) {
        $('#sub-categories').select2({
            ajax: {
                url: window.location.origin + '/admin/item/get-categories',
                data: function(params) {
                    return {
                        q: params.term || '', // search term
                        page: params.page || 1,
                        module_id: window.currentModuleId || null,
                        parent_id: $('#category_id').val() || 0,
                        sub_category: true
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                error: function(xhr, status, error) {
                    console.error('Sub-category dropdown error:', error);
                }
            },
            placeholder: 'Select a sub-category',
            allowClear: true,
            minimumInputLength: 0
        });
    }

    // Initialize brand dropdown
    if (debugElement('brand_id')) {
        $('#brand_id').select2({
            ajax: {
                url: window.location.origin + '/admin/brand/get-all',
                data: function(params) {
                    return {
                        q: params.term || '', // search term
                        page: params.page || 1,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                error: function(xhr, status, error) {
                    console.error('Brand dropdown error:', error);
                }
            },
            placeholder: 'Select a brand',
            allowClear: true,
            minimumInputLength: 0
        });
    }

    // Initialize condition dropdown
    if (debugElement('condition_id')) {
        $('#condition_id').select2({
            ajax: {
                url: window.location.origin + '/admin/common-condition/get-all',
                data: function(params) {
                    return {
                        q: params.term || '', // search term
                        page: params.page || 1,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                error: function(xhr, status, error) {
                    console.error('Condition dropdown error:', error);
                }
            },
            placeholder: 'Select a condition',
            allowClear: true,
            minimumInputLength: 0
        });
    }

    // Initialize unit dropdown
    if (debugElement('unit')) {
        $('#unit').select2({
            placeholder: 'Select a unit',
            allowClear: true
        });
    }

    // Handle category change to update sub-categories
    $('#category_id').on('change', function() {
        const categoryId = $(this).val();
        console.log('Category changed to:', categoryId);
        
        // Clear sub-category dropdown
        $('#sub-categories').empty().append('<option value="">Select a sub-category</option>');
        
        if (categoryId) {
            // Load sub-categories
            $.ajax({
                url: window.location.origin + '/admin/item/get-categories',
                data: {
                    parent_id: categoryId,
                    module_id: window.currentModuleId || null,
                    sub_category: true
                },
                success: function(data) {
                    if (data && data.length > 0) {
                        data.forEach(function(item) {
                            $('#sub-categories').append(
                                $('<option></option>').val(item.id).text(item.text)
                            );
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading sub-categories:', error);
                }
            });
        }
    });

    // Debug current module ID
    console.log('Current Module ID:', window.currentModuleId);
    
    // Test API endpoints
    function testEndpoints() {
        const endpoints = [
            '/admin/store/get-stores',
            '/admin/item/get-categories',
            '/admin/brand/get-all',
            '/admin/common-condition/get-all'
        ];
        
        endpoints.forEach(function(endpoint) {
            $.ajax({
                url: window.location.origin + endpoint,
                data: { q: '', page: 1 },
                success: function(data) {
                    console.log(`✅ ${endpoint} working:`, data.length || 'No data');
                },
                error: function(xhr, status, error) {
                    console.error(`❌ ${endpoint} error:`, error);
                }
            });
        });
    }
    
    // Test endpoints after a short delay
    setTimeout(testEndpoints, 1000);
});

// Global error handler for AJAX requests
$(document).ajaxError(function(event, xhr, settings, error) {
    console.error('AJAX Error:', {
        url: settings.url,
        status: xhr.status,
        error: error
    });
}); 