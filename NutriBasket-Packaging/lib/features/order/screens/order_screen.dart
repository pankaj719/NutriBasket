import 'package:sixam_mart_delivery/features/order/controllers/order_controller.dart';
import 'package:sixam_mart_delivery/util/dimensions.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:sixam_mart_delivery/features/order/widgets/history_order_widget.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

class OrderScreen extends StatefulWidget {
  const OrderScreen({super.key});

  @override
  State<OrderScreen> createState() => _OrderScreenState();
}

class _OrderScreenState extends State<OrderScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final List<String> _tabs = ['all', 'packaging', 'picked_up', 'status'];
  String _selectedStatus = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    Get.find<OrderController>().getCompletedOrders(1);
    Get.find<OrderController>().getPackagingOrders();
    Get.find<OrderController>().getPickedUpOrders();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final ScrollController scrollController = ScrollController();
    return Scaffold(
      appBar:
          CustomAppBarWidget(title: 'my_orders'.tr, isBackButtonExist: false),
      body: Column(
        children: [
          TabBar(
            controller: _tabController,
            tabs: [
              Tab(text: 'all'.tr),
              Tab(text: 'packaging'.tr),
              Tab(text: 'picked_up'.tr),
              Tab(text: 'status'.tr),
            ],
            onTap: (index) async {
              if (_tabs[index] == 'packaging') {
                await Get.find<OrderController>().getPackagingOrders();
              } else if (_tabs[index] == 'picked_up') {
                await Get.find<OrderController>().getPickedUpOrders();
              } else if (_tabs[index] == 'all') {
                await Get.find<OrderController>().getCompletedOrders(1);
              }
              setState(() {});
            },
          ),
          Expanded(
            child: GetBuilder<OrderController>(builder: (orderController) {
              List orderList;
              if (_tabController.index == 1) {
                orderList = orderController.packagingOrderList ?? [];
              } else if (_tabController.index == 2) {
                orderList = orderController.pickedUpOrderList ?? [];
              } else if (_tabController.index == 3) {
                orderList = orderController.statusOrderList ?? [];
              } else {
                orderList = orderController.completedOrderList ?? [];
              }
              return orderList.isNotEmpty
                  ? RefreshIndicator(
                      onRefresh: () async {
                        if (_tabController.index == 1) {
                          await orderController.getPackagingOrders();
                        } else if (_tabController.index == 2) {
                          await orderController.getPickedUpOrders();
                        } else if (_tabController.index == 3) {
                          await orderController
                              .getOrdersByStatus(_selectedStatus);
                        } else {
                          await orderController.getCompletedOrders(1);
                        }
                      },
                      child: SingleChildScrollView(
                        controller: scrollController,
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: Center(
                          child: SizedBox(
                            width: 1170,
                            child: Column(
                              children: [
                                if (_tabController.index == 3)
                                  Padding(
                                    padding: const EdgeInsets.all(8.0),
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: TextField(
                                            decoration: InputDecoration(
                                              labelText: 'Enter status',
                                              border: OutlineInputBorder(),
                                            ),
                                            onChanged: (val) {
                                              _selectedStatus = val;
                                            },
                                            onSubmitted: (val) async {
                                              _selectedStatus = val;
                                              await orderController
                                                  .getOrdersByStatus(
                                                      _selectedStatus);
                                              setState(() {});
                                            },
                                          ),
                                        ),
                                        IconButton(
                                          icon: Icon(Icons.search),
                                          onPressed: () async {
                                            await orderController
                                                .getOrdersByStatus(
                                                    _selectedStatus);
                                            setState(() {});
                                          },
                                        ),
                                      ],
                                    ),
                                  ),
                                ListView.builder(
                                  padding: const EdgeInsets.all(
                                      Dimensions.paddingSizeSmall),
                                  itemCount: orderList.length,
                                  physics: const NeverScrollableScrollPhysics(),
                                  shrinkWrap: true,
                                  itemBuilder: (context, index) {
                                    return HistoryOrderWidget(
                                        orderModel: orderList[index],
                                        isRunning: false,
                                        index: index);
                                  },
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    )
                  : Center(child: Text('no_order_found'.tr));
            }),
          ),
        ],
      ),
    );
  }
}
