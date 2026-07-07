import pandas as pd

# 1️⃣ Load contract items
contract_items = pd.read_excel("E://2centscapital/Excel Script/contract_items_template.xlsx")

# 2️⃣ Load customer list with correct header row (Row 9)
customer_list = pd.read_excel(
    "E://2centscapital/Excel Script/Customer_List_Updated.xlsb.xlsx",
    header=8  # Row index 8 means Excel row 9
)

# 3️⃣ Clean column names
customer_list.columns = customer_list.columns.str.strip()

# 4️⃣ Rename for convenience
customer_list = customer_list.rename(columns={
    'Product code': 'ProductCode',
    'Product Name': 'ProductName',
})

# 5️⃣ Remove non-data rows
customer_list = customer_list[
    customer_list['ProductCode'].notna() & customer_list['ProductName'].notna()
]

# 6️⃣ Make lowercase for safe matching
contract_items['ItemName_Lower'] = contract_items['ItemName'].str.lower().str.strip()
customer_list['ProductName_Lower'] = customer_list['ProductName'].str.lower().str.strip()

# 7️⃣ Merge on lowercase names, bring only Conversation Room Price
merged = pd.merge(
    contract_items,
    customer_list[['ProductName_Lower', 'Conversation Room']],
    left_on='ItemName_Lower',
    right_on='ProductName_Lower',
    how='left'
)

# 8️⃣ Fill Price with Conversation Room Price
merged['Price'] = merged['Conversation Room']

# 9️⃣ Drop helper columns
merged = merged.drop(columns=['ItemName_Lower', 'ProductName_Lower', 'Conversation Room'])

# 🔟 Save final file
merged.to_excel("contract_items_with_conversationroom_prices.xlsx", index=False)
print("✅ New file saved as 'contract_items_with_conversationroom_prices.xlsx'")

# Show any items missing Conversation Room Price
missing = merged[merged['Price'].isna()]
print(f"⚠️ {len(missing)} items did not get a Conversation Room Price:")
print(missing[['ItemName']])
