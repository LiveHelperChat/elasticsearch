    <div class="row">
        <div class="col-4">
            <table class="table table-sm">
                <tr class="text-warning">
                    <th width="1%"></th>
                    <th>Pending chat ID</th>
                    <th>Index time</th>
                    <th>Dep ID</th>
                </tr>
                <?php $counter = 0; foreach ($chats as $chat) : if ($chat->status == 0) : $counter++;?>
                    <tr>
                        <td><?php echo $counter?>. </td>
                        <td><a class="material-icons" id="preview-item-<?php echo $chat->chat_id?>" data-list-navigate="true" onclick="lhc.previewChat(<?php echo $chat->chat_id?>,this)">info_outline</a><?php echo htmlspecialchars($chat->chat_id);?></td>
                        <td><?php echo date('Y-m-d H:i:s',$chat->itime/1000);?></td>
                        <td><a class="live-help-tooltip" data-placement="top" data-bs-toggle="tooltip" data-bs-original-title="<?php echo htmlspecialchars($chat->department); ?>"><?php echo $chat->dep_id;?></a></td>
                    </tr>
                <?php endif; endforeach; ?>
                <tr class="text-success">
                    <th width="1%"></th>
                    <th>Active chat ID</th>
                    <th>Index time</th>
                    <th>Dep ID</th>
                </tr>
                <?php $counter = 0; foreach ($chats as $chat) : if ($chat->status == 1) : $counter++;?>
                    <tr>
                        <td><?php echo $counter?>. </td>
                        <td><a data-placement="top" data-bs-toggle="tooltip" data-bs-original-title="User ID - <?php echo htmlspecialchars(is_object($chat->chat) ? $chat->chat->user_id : 0); ?>" class="material-icons live-help-tooltip" id="preview-item-<?php echo $chat->chat_id?>" data-list-navigate="true" onclick="lhc.previewChat(<?php echo $chat->chat_id?>,this)">info_outline</a><?php echo htmlspecialchars($chat->chat_id);?></td>
                        <td><?php echo date('Y-m-d H:i:s',$chat->itime/1000);?></td>
                        <td>
                            <a class="live-help-tooltip" data-placement="top" data-bs-toggle="tooltip" data-bs-original-title="<?php echo htmlspecialchars($chat->department); ?>"><?php echo $chat->dep_id;?></a>
                        </td>
                    </tr>
                <?php endif; endforeach; ?>
            </table>
        </div>

        <div class="col-8">
            <table class="table table-sm">
                <tr>
                    <th>User ID</th>
                    <th>Index time</th>
                    <th>Slots</th>
                    <th>Pending C.</th>
                    <th>Active C.</th>
                    <th>Inactive C.</th>
                    <th>Free slots</th>
                    <th>Live C.</th>
                    <th>Dep.</th>
                </tr>
                <?php $totals = [
                        'max_chats' => 0,
                        'pending_chats' => 0,
                        'active_chats' => 0,
                        'inactive_chats' => 0,
                        'free_slots' => 0,
                        'live_chats' => 0
                ]; foreach ($operators as $operator) : ?>
                <tr>
                    <td><a href="#" title="See operator statistic" onclick="lhc.revealModal({'url':WWW_DIR_JAVASCRIPT+'statistic/userstats/<?php echo $operator->user_id;?>?ts=<?php echo $operator->itime/1000?>'})"><span class="material-icons">bar_chart</span></a><a class="live-help-tooltip" data-placement="top" data-bs-toggle="tooltip" data-bs-original-title="<?php echo htmlspecialchars($operator->plain_user_name); ?>"><?php echo $operator->user_id;?></a></td>
                    <td><?php echo date('Y-m-d H:i:s',$operator->itime/1000);?></td>
                    <td><?php echo $operator->max_chats; $totals['max_chats'] += $operator->max_chats; ?></td>
                    <td><?php echo $operator->pending_chats; $totals['pending_chats'] += $operator->pending_chats;?></td>
                    <td><?php echo $operator->active_chats; $totals['active_chats'] += $operator->active_chats;?></td>
                    <td><?php echo $operator->inactive_chats; $totals['inactive_chats'] += $operator->inactive_chats;?></td>
                    <td><?php echo $operator->free_slots; $totals['free_slots'] += $operator->free_slots;?></td>
                    <td><?php echo $operator->active_chats + $operator->pending_chats - $operator->inactive_chats; $totals['live_chats'] += $operator->active_chats + $operator->pending_chats - $operator->inactive_chats;?></td>
                    <td>
                        <a class="live-help-tooltip" data-placement="top" data-bs-toggle="tooltip" data-bs-original-title="<?php echo json_encode($operator->dep_ids); ?>"><i class="material-icons">info_outline</i></a>
                    </td>
                </tr>
                <?php endforeach;?>
                <tr>
                    <th colspan="2" class="text-end">Totals</th>
                    <th><?php echo $totals['max_chats']; ?> (<?php echo round($totals['max_chats'] / $divide, 2);?>)</th>
                    <th><?php echo $totals['pending_chats']; ?> (<?php echo round($totals['pending_chats'] / $divide, 2);?>)</th>
                    <th><?php echo $totals['active_chats']; ?> (<?php echo round($totals['active_chats'] / $divide, 2);?>)</th>
                    <th><?php echo $totals['inactive_chats']; ?> (<?php echo round($totals['inactive_chats'] / $divide, 2);?>)</th>
                    <th><?php echo $totals['free_slots']; ?> (<?php echo round($totals['free_slots'] / $divide, 2);?>)</th>
                    <th colspan="2"><?php echo $totals['live_chats']; ?> (<?php echo round($totals['live_chats'] / $divide, 2);?>)</th>
                </tr>
            </table>
            <ul>
                <li>Some operators from active chats might not be listed as online.</li>
            </ul>
        </div>
    </div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>



