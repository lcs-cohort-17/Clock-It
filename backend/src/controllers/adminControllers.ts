import { supabase } from "../config/supabase.js";
import type { Request, Response } from "express";

// 1. GET /api/admin/attendance - Clock events only
export const getAttendance = async (req: Request, res: Response) => {
  try {
    const { staff_id, sync_status, start_date, end_date } = req.query;
    
    let query = supabase
      .from("attendance_events")
      .select("*")
      .in("type", ["clock_in", "clock_out"]) // Only clock events per ticket
      .order("timestamp", { ascending: false });

    if (staff_id) query = query.eq("staff_id", staff_id as string);
    if (sync_status) query = query.eq("sync_status", sync_status as string);
    if (start_date && end_date) {
      query = query.gte("timestamp", start_date as string).lte("timestamp", end_date as string);
    }

    const { data, error } = await query;
    if (error) return res.status(500).json({ error: error.message });
    return res.json(data);
  } catch (err: any) {
    return res.status(500).json({ error: err.message });
  }
};

// 2. GET /api/admin/leave - Leave/sick requests
export const getLeave = async (req: Request, res: Response) => {
  try {
    const { staff_id, status, type } = req.query;
    
    let query = supabase
      .from("leave_requests")
      .select("*")
      .order("created_at", { ascending: false });

    if (staff_id) query = query.eq("staff_id", staff_id as string);
    if (status) query = query.eq("status", status as string);
    if (type) query = query.eq("type", type as string);

    const { data, error } = await query;
    if (error) return res.status(500).json({ error: error.message });
    return res.json(data);
  } catch (err: any) {
    return res.status(500).json({ error: err.message });
  }
};

// 3. GET /api/admin/audit - Audit trail
export const getAudit = async (req: Request, res: Response) => {
  try {
    const { table_name, record_id } = req.query;
    
    let query = supabase
      .from("audit_logs")
      .select("*")
      .order("changed_at", { ascending: false });

    if (table_name) query = query.eq("table_name", table_name as string);
    if (record_id) query = query.eq("record_id", record_id as string);

    const { data, error } = await query;
    if (error) return res.status(500).json({ error: error.message });
    return res.json(data);
  } catch (err: any) {
    return res.status(500).json({ error: err.message });
  }
};

// 4. PATCH /api/admin/attendance/:id - Override + auto audit log
export const overrideAttendance = async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const { timestamp, location, admin_id } = req.body;

    // 1. Get old values for audit
    const { data: oldData, error: fetchError } = await supabase
      .from("attendance_events")
      .select("*")
      .eq("id", id)
      .single();

    if (fetchError) return res.status(404).json({ error: "Record not found" });

    // 2. Update
    const { data: newData, error: updateError } = await supabase
      .from("attendance_events")
      .update({ timestamp, location, updated_by: admin_id })
      .eq("id", id)
      .select()
      .single();

    if (updateError) return res.status(500).json({ error: updateError.message });

    // 3. Auto-log to audit trail
    await supabase.from("audit_logs").insert({
      table_name: "attendance_events",
      record_id: id,
      action: "override",
      changed_by: admin_id,
      old_values: oldData,
      new_values: newData
    });

    return res.json(newData);
  } catch (err: any) {
    return res.status(500).json({ error: err.message });
  }
};