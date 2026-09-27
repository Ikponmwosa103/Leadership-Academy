import { NextResponse } from "next/server";
import { getPool, ensureTables } from "../../../lib/db";
export async function POST(req){
 try{
  const d=await req.json(); const name=String(d.name||"").trim(),email=String(d.email||"").trim().toLowerCase(),subject=String(d.subject||"General Inquiry").trim(),message=String(d.message||"").trim();
  if(!name||!email||!message)return NextResponse.json({success:false,message:"Please fill in your name, email, and message."},{status:422});
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))return NextResponse.json({success:false,message:"Please enter a valid email address."},{status:422});
  if(name.length>120||subject.length>80||message.length>10000)return NextResponse.json({success:false,message:"One of your fields is too long."},{status:422});
  await ensureTables(); await getPool().execute("INSERT INTO contact_messages(name,email,subject,message) VALUES(?,?,?,?)",[name,email,subject||"General Inquiry",message]);
  return NextResponse.json({success:true,message:"Your message has been received. We will be in touch soon."});
 }catch(e){console.error(e);return NextResponse.json({success:false,message:"We could not save your message. Please try again later."},{status:500})}
}